<?php

namespace PubNubTests\integrational\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Endpoints\Access\GrantToken;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Access\Permissions;
use PubNub\PNConfiguration;
use PubNub\PubNub;

/**
 * DataSync grants issued by a real Access Manager and spent against the real data plane.
 *
 * Two clients are involved: an admin signing its requests with the secret key, which grants the
 * tokens and owns the fixtures, and a token-only client that has no credentials beyond the token
 * under test. That split is what makes the denials meaningful - a client carrying both a signature
 * and a token is rejected outright, so the token is the only thing granting the access asserted
 * here.
 *
 * Skipped unless DATASYNC_SUBSCRIBE_KEY, DATASYNC_PUBLISH_KEY, DATASYNC_SECRET_KEY and
 * DATASYNC_ENTITY_CLASS are set.
 */
class DataSyncGrantTest extends TestCase
{
    /** The uuid every token here is issued to, and the only one allowed to spend it. */
    private const CLIENT_UUID = 'php-sdk-pam-client';

    /** Access Manager needs a moment before a freshly granted token is honoured. */
    private const TOKEN_PROPAGATION_SECONDS = 1;

    private PubNub $admin;

    private string $subscribeKey;

    private string $publishKey;

    private string $entityClass;

    /** @var string[] */
    private array $createdEntityIds = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->subscribeKey = getenv('DATASYNC_SUBSCRIBE_KEY') ?: '';
        $this->publishKey = getenv('DATASYNC_PUBLISH_KEY') ?: '';
        $secretKey = getenv('DATASYNC_SECRET_KEY') ?: '';
        $this->entityClass = getenv('DATASYNC_ENTITY_CLASS') ?: '';

        if (
            $this->subscribeKey === '' || $this->publishKey === ''
            || $secretKey === '' || $this->entityClass === ''
        ) {
            $this->markTestSkipped(
                'Set DATASYNC_SUBSCRIBE_KEY, DATASYNC_PUBLISH_KEY, DATASYNC_SECRET_KEY and '
                    . 'DATASYNC_ENTITY_CLASS to run the DataSync grant tests against a live keyset.'
            );
        }

        $config = $this->configuration('php-sdk-pam-admin');
        $config->setSecretKey($secretKey);

        $this->admin = new PubNub($config);
        $this->createdEntityIds = [];
    }

    public function tearDown(): void
    {
        foreach ($this->createdEntityIds as $entityId) {
            try {
                $this->admin->dataSync()->deleteEntity()->entityId($entityId)->sync();
            } catch (PubNubServerException $exception) {
                // best-effort cleanup
            }
        }

        parent::tearDown();
    }

    private function configuration(string $uuid): PNConfiguration
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey($this->subscribeKey);
        $config->setPublishKey($this->publishKey);
        $config->setUuid($uuid);

        if ($origin = getenv('DATASYNC_ORIGIN')) {
            $config->setOrigin($origin);
        }

        return $config;
    }

    /**
     * A client whose only credential is the token, so anything it can do, the token allows.
     */
    private function clientWithToken(string $token): PubNub
    {
        $client = new PubNub($this->configuration(self::CLIENT_UUID));
        $client->setToken($token);

        return $client;
    }

    /**
     * @param callable(GrantToken): void $configure
     */
    private function grant(callable $configure): string
    {
        $endpoint = $this->admin->grantToken()
            ->ttl(60)
            ->authorizedUuid(self::CLIENT_UUID);

        $configure($endpoint);

        $token = $endpoint->sync();
        $this->assertNotEmpty($token);

        sleep(self::TOKEN_PROPAGATION_SECONDS);

        return $token;
    }

    private function createEntity(?string $entityId = null): string
    {
        $entityId = $entityId ?? 'php-sdk-pam-' . uniqid();

        $this->admin->dataSync()->createEntity()
            ->entityId($entityId)
            ->entityClass($this->entityClass)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['make' => 'Toyota'])
            ->sync();

        $this->createdEntityIds[] = $entityId;

        return $entityId;
    }

    /**
     * @param callable $call
     */
    private function assertDenied(callable $call, string $message): void
    {
        try {
            $call();
        } catch (PubNubServerException $exception) {
            $this->assertSame(403, $exception->getStatusCode(), $message . ' (wrong status code)');
            return;
        }

        $this->fail($message);
    }

    /**
     * @param Permissions|false $granted
     */
    private function granted($granted, string $message): Permissions
    {
        $this->assertInstanceOf(Permissions::class, $granted, $message);

        /** @var Permissions $granted */
        return $granted;
    }

    public function testEntityResourceGrantAllowsOnlyTheGrantedEntity(): void
    {
        $grantedId = $this->createEntity();
        $otherId = $this->createEntity();

        $token = $this->grant(function (GrantToken $grant) use ($grantedId): void {
            $grant->addDataSyncEntityResources([$grantedId => ['get' => true]]);
        });

        $client = $this->clientWithToken($token);

        $allowed = $client->dataSync()->getEntity()->entityId($grantedId)->sync();
        $this->assertSame($grantedId, $allowed->getId());

        $this->assertDenied(
            fn() => $client->dataSync()->getEntity()->entityId($otherId)->sync(),
            'an entity outside the grant must not be readable'
        );
    }

    public function testEntityPatternGrantAllowsOnlyMatchingIds(): void
    {
        $prefix = 'phppam' . substr(uniqid(), -8);
        $matchingId = $this->createEntity($prefix . '-match');
        $nonMatchingId = $this->createEntity();

        $token = $this->grant(function (GrantToken $grant) use ($prefix): void {
            $grant->addDataSyncEntityPatterns(['^' . $prefix . '-.*$' => ['get' => true]]);
        });

        $client = $this->clientWithToken($token);

        $allowed = $client->dataSync()->getEntity()->entityId($matchingId)->sync();
        $this->assertSame($matchingId, $allowed->getId());

        $this->assertDenied(
            fn() => $client->dataSync()->getEntity()->entityId($nonMatchingId)->sync(),
            'an id the pattern does not match must not be readable'
        );
    }

    public function testGetOnlyGrantDeniesWrites(): void
    {
        $entityId = $this->createEntity();

        $token = $this->grant(function (GrantToken $grant) use ($entityId): void {
            $grant->addDataSyncEntityResources([$entityId => ['get' => true]]);
        });

        $client = $this->clientWithToken($token);

        $read = $client->dataSync()->getEntity()->entityId($entityId)->sync();
        $this->assertSame('active', $read->getData()->getStatus());

        $this->assertDenied(
            fn() => $client->dataSync()->setEntity()
                ->entityId($entityId)
                ->entityClassVersion(1)
                ->status('updated')
                ->payload(['make' => 'Honda'])
                ->sync(),
            'a get-only grant must not allow a replace'
        );

        $this->assertDenied(
            fn() => $client->dataSync()->deleteEntity()->entityId($entityId)->sync(),
            'a get-only grant must not allow a delete'
        );
    }

    public function testUpdateGrantAllowsWritingTheGrantedEntity(): void
    {
        $entityId = $this->createEntity();

        $token = $this->grant(function (GrantToken $grant) use ($entityId): void {
            $grant->addDataSyncEntityResources([$entityId => ['get' => true, 'update' => true]]);
        });

        $client = $this->clientWithToken($token);

        $updated = $client->dataSync()->setEntity()
            ->entityId($entityId)
            ->entityClassVersion(1)
            ->status('updated')
            ->payload(['make' => 'Honda'])
            ->sync();

        $this->assertSame('updated', $updated->getData()->getStatus());

        // Update does not imply delete, so the narrower operation is still refused.
        $this->assertDenied(
            fn() => $client->dataSync()->deleteEntity()->entityId($entityId)->sync(),
            'update permission must not imply delete'
        );
    }

    /**
     * The projection travels in the token's meta rather than its permissions, so the proof that
     * the SDK encodes it the way Access Manager expects is that the data plane accepts the token.
     */
    public function testProjectionScopedGrantIsAcceptedByTheDataPlane(): void
    {
        $entityId = $this->createEntity();

        $token = $this->grant(function (GrantToken $grant) use ($entityId): void {
            $grant
                ->addDataSyncEntityResources([$entityId => ['get' => true]])
                ->dataSyncProjections([
                    'resources' => ['entities' => [$entityId => '__default__']],
                ]);
        });

        $client = $this->clientWithToken($token);
        $read = $client->dataSync()->getEntity()->entityId($entityId)->sync();

        $this->assertSame($entityId, $read->getId());
        $this->assertSame(['make' => 'Toyota'], $read->getData()->getPayload());

        $projections = $this->admin->parseToken($token)->getDataSyncProjections();
        $this->assertNotNull($projections);
        $this->assertSame('__default__', $projections->getResources()->getEntityProjection($entityId));
    }

    /**
     * Everything the SDK can put into a token has to survive the round trip through the service
     * and back out of parseToken(), including the scopes and projection families the data plane
     * tests above never touch.
     */
    public function testGrantedTokenParsesBackIntoItsDataSyncScopes(): void
    {
        $token = $this->grant(function (GrantToken $grant): void {
            $grant
                ->addDataSyncEntityResources(['vehicle-1' => ['get' => true, 'update' => true]])
                ->addDataSyncRelationshipResources(['rel-1' => ['get' => true]])
                ->addDataSyncMembershipResources(['mem-1' => ['get' => true, 'delete' => true]])
                ->addDataSyncEntityPatterns(['^vehicle-.*$' => ['get' => true]])
                ->addUuidResources(['user-1' => ['get' => true]])
                ->dataSyncProjections([
                    'resources' => [
                        'entities' => ['vehicle-1' => '__default__'],
                        'users' => ['user-1' => '__default__'],
                        'memberships' => ['mem-1' => '__default__'],
                    ],
                    'patterns' => [
                        'entities' => ['^vehicle-.*$' => '__default__'],
                    ],
                ]);
        });

        $parsed = $this->admin->parseToken($token);

        $this->assertSame(self::CLIENT_UUID, $parsed->getUuid());

        $entity = $this->granted($parsed->getDataSyncEntityResource('vehicle-1'), 'entity resource');
        $this->assertTrue($entity->hasGet());
        $this->assertTrue($entity->hasUpdate());
        $this->assertFalse($entity->hasDelete());

        $this->assertTrue($this->granted(
            $parsed->getDataSyncRelationshipResource('rel-1'),
            'relationship resource'
        )->hasGet());

        $membership = $this->granted($parsed->getDataSyncMembershipResource('mem-1'), 'membership resource');
        $this->assertTrue($membership->hasGet());
        $this->assertTrue($membership->hasDelete());

        $this->assertTrue($this->granted(
            $parsed->getDataSyncEntityPattern('^vehicle-.*$'),
            'entity pattern'
        )->hasGet());

        $this->assertFalse($parsed->getDataSyncEntityResource('vehicle-2'));

        $projections = $parsed->getDataSyncProjections();
        $this->assertNotNull($projections);

        $resources = $projections->getResources();
        $this->assertSame('__default__', $resources->getEntityProjection('vehicle-1'));
        $this->assertSame('__default__', $resources->getUserProjection('user-1'));
        $this->assertSame('__default__', $resources->getMembershipProjection('mem-1'));
        $this->assertSame('__default__', $projections->getPatterns()->getEntityProjection('^vehicle-.*$'));
    }
}
