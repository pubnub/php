<?php

namespace PubNubTests\integrational\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Endpoints\Access\GrantToken;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\DataSync\PNDataSyncPatch;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTests\helpers\RetriesDataSyncReads;

/**
 * What a DataSync projection actually hides, proven against the live data plane.
 *
 * The grant test only shows that a projected token is accepted; these show the effect of the
 * projection itself, which is the part no offline test can reach: the same record read through
 * two tokens comes back with different fields.
 *
 * Needs DATASYNC_ENTITY_CLASS_PROJECTIONS to name an entity class whose properties are split
 * across two projections - PROJECTED_FIELDS below in the base "__default__" one and
 * ADMIN_ONLY_FIELDS in a second one named "admin".
 */
class DataSyncProjectionTest extends TestCase
{
    use RetriesDataSyncReads;

    private const CLIENT_UUID = 'php-sdk-projection-client';

    private const ADMIN_PROJECTION = 'admin';
    private const DEFAULT_PROJECTION = '__default__';

    private const TOKEN_PROPAGATION_SECONDS = 1;

    /** Fields every projection of the class exposes. */
    private const PROJECTED_FIELDS = ['model' => 'Camry', 'owner' => 'Alice'];

    /** Fields only the "admin" projection exposes. */
    private const ADMIN_ONLY_FIELDS = ['dateBought' => '2024-01-01', 'comments' => 'first owner'];

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
        $this->entityClass = getenv('DATASYNC_ENTITY_CLASS_PROJECTIONS') ?: '';

        if (
            $this->subscribeKey === '' || $this->publishKey === ''
            || $secretKey === '' || $this->entityClass === ''
        ) {
            $this->markTestSkipped(
                'Set DATASYNC_SUBSCRIBE_KEY, DATASYNC_PUBLISH_KEY, DATASYNC_SECRET_KEY and '
                    . 'DATASYNC_ENTITY_CLASS_PROJECTIONS to run the DataSync projection tests.'
            );
        }

        $config = $this->configuration('php-sdk-projection-admin');
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
        // Ten seconds is the default and a loaded CI runner occasionally needs more than that.
        $config->setNonSubscribeRequestTimeout(30);

        if ($origin = getenv('DATASYNC_ORIGIN')) {
            $config->setOrigin($origin);
        }

        return $config;
    }

    /**
     * Creates a vehicle through the full-access admin with every projected field populated, so
     * both projections have something to reveal or hide.
     */
    private function createVehicle(?string $entityId = null): string
    {
        $entityId = $entityId ?? 'php-sdk-projection-' . uniqid();

        $this->admin->dataSync()->createEntity()
            ->entityId($entityId)
            ->entityClass($this->entityClass)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(array_merge(self::PROJECTED_FIELDS, self::ADMIN_ONLY_FIELDS))
            ->sync();

        $this->createdEntityIds[] = $entityId;

        // A projection-scoped token is granted over this entity next.
        $this->readableNow(
            fn() => $this->admin->dataSync()->getEntity()->entityId($entityId)->sync(),
            'the vehicle fixture'
        );

        return $entityId;
    }

    /**
     * @param callable(GrantToken): void $configure
     */
    private function clientWithGrant(callable $configure): PubNub
    {
        $endpoint = $this->admin->grantToken()
            ->ttl(60)
            ->authorizedUuid(self::CLIENT_UUID);

        $configure($endpoint);

        $client = new PubNub($this->configuration(self::CLIENT_UUID));
        $client->setToken($endpoint->sync());

        sleep(self::TOKEN_PROPAGATION_SECONDS);

        return $client;
    }

    /**
     * Grants get on one entity, seen through one projection.
     *
     * @param array<string, bool> $permissions
     */
    private function clientSeeing(
        string $entityId,
        string $projection,
        array $permissions = ['get' => true]
    ): PubNub {
        return $this->clientWithGrant(function (GrantToken $grant) use ($entityId, $projection, $permissions): void {
            $grant
                ->addDataSyncEntityResources([$entityId => $permissions])
                ->dataSyncProjections([
                    'resources' => ['entities' => [$entityId => $projection]],
                ]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(PubNub $client, string $entityId): array
    {
        $payload = $client->dataSync()->getEntity()->entityId($entityId)->sync()->getData()->getPayload();
        $this->assertNotNull($payload);

        return $payload;
    }

    public function testDefaultProjectionHidesTheAdminOnlyFields(): void
    {
        $entityId = $this->createVehicle();
        $client = $this->clientSeeing($entityId, self::DEFAULT_PROJECTION);

        $payload = $this->readPayload($client, $entityId);

        foreach (self::PROJECTED_FIELDS as $field => $value) {
            $this->assertSame($value, $payload[$field] ?? null, "$field belongs to the base projection");
        }

        foreach (array_keys(self::ADMIN_ONLY_FIELDS) as $field) {
            $this->assertArrayNotHasKey($field, $payload, "$field is admin-only");
        }
    }

    public function testAdminProjectionRevealsEveryField(): void
    {
        $entityId = $this->createVehicle();
        $client = $this->clientSeeing($entityId, self::ADMIN_PROJECTION);

        $payload = $this->readPayload($client, $entityId);

        foreach (array_merge(self::PROJECTED_FIELDS, self::ADMIN_ONLY_FIELDS) as $field => $value) {
            $this->assertSame($value, $payload[$field] ?? null, "$field should be visible to admin");
        }
    }

    public function testPatternProjectionAppliesToMatchingIds(): void
    {
        $prefix = 'phpproj' . substr(uniqid(), -8);
        $entityId = $this->createVehicle($prefix . '-match');
        $pattern = '^' . $prefix . '-.*$';

        $client = $this->clientWithGrant(function (GrantToken $grant) use ($pattern): void {
            $grant
                ->addDataSyncEntityPatterns([$pattern => ['get' => true]])
                ->dataSyncProjections([
                    'patterns' => ['entities' => [$pattern => self::DEFAULT_PROJECTION]],
                ]);
        });

        $payload = $this->readPayload($client, $entityId);

        $this->assertArrayHasKey('model', $payload);
        $this->assertArrayNotHasKey('dateBought', $payload, 'the pattern projection still hides admin fields');
    }

    public function testDefaultProjectionCanWriteTheFieldsItSees(): void
    {
        $entityId = $this->createVehicle();
        $client = $this->clientSeeing($entityId, self::DEFAULT_PROJECTION, ['get' => true, 'update' => true]);

        $client->dataSync()->updateEntity()
            ->entityId($entityId)
            ->patch((new PNDataSyncPatch())->replace('/payload/model', 'UpdatedModel'))
            ->sync();

        // Read back through the admin, which sees everything: the visible field changed and the
        // fields the client could not see survived the write untouched.
        $full = $this->admin->dataSync()->getEntity()->entityId($entityId)->sync()->getData()->getPayload();

        $this->assertSame('UpdatedModel', $full['model'] ?? null);

        foreach (self::ADMIN_ONLY_FIELDS as $field => $value) {
            $this->assertSame($value, $full[$field] ?? null, "$field must survive a base-projection write");
        }
    }

    public function testDefaultProjectionCannotWriteAnAdminOnlyField(): void
    {
        $entityId = $this->createVehicle();
        $client = $this->clientSeeing($entityId, self::DEFAULT_PROJECTION, ['get' => true, 'update' => true]);

        try {
            $client->dataSync()->updateEntity()
                ->entityId($entityId)
                ->patch((new PNDataSyncPatch())->replace('/payload/dateBought', '2030-12-31'))
                ->sync();
        } catch (PubNubServerException $exception) {
            // Refusing the write outright is one valid outcome; dropping the invisible field from
            // an otherwise accepted request is the other. The check below covers both.
        }

        $full = $this->admin->dataSync()->getEntity()->entityId($entityId)->sync()->getData()->getPayload();

        $this->assertSame(
            self::ADMIN_ONLY_FIELDS['dateBought'],
            $full['dateBought'] ?? null,
            'writing an admin-only field through the base projection must not persist'
        );
    }
}
