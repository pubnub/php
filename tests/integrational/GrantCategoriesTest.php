<?php

namespace PubNubTests\integrational;

use PHPUnit\Framework\TestCase;
use PubNub\Endpoints\Access\GrantToken;
use PubNub\Enums\PNGrantCategory;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Access\Permissions;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerAbstractResult;
use PubNub\PNConfiguration;
use PubNub\PubNub;

/**
 * Category grants issued by a real Access Manager and spent against App Context list calls.
 *
 * The keyset must have Get All enforcement enabled, so that listing all channel or uuid metadata
 * requires a category grant. The admin signs with the secret key; each client carries nothing but
 * the token or auth key under test, because the secret key bypasses Access Manager.
 *
 * Skipped unless CATEGORY_SUBSCRIBE_KEY, CATEGORY_PUBLISH_KEY and CATEGORY_SECRET_KEY are set.
 */
class GrantCategoriesTest extends TestCase
{
    /** Grants take effect asynchronously, so list calls are retried for up to this long. */
    private const PROPAGATION_TIMEOUT_SECONDS = 30;

    /** Revokes currently take over a minute to propagate, in both PAMv2 and PAMv3. */
    private const REVOKE_PROPAGATION_TIMEOUT_SECONDS = 90;

    private const POLL_INTERVAL_MICROSECONDS = 500000;

    private PubNub $admin;

    private string $subscribeKey;

    private string $publishKey;

    public function setUp(): void
    {
        parent::setUp();

        $this->subscribeKey = getenv('CATEGORY_SUBSCRIBE_KEY') ?: '';
        $this->publishKey = getenv('CATEGORY_PUBLISH_KEY') ?: '';
        $secretKey = getenv('CATEGORY_SECRET_KEY') ?: '';

        if ($this->subscribeKey === '' || $this->publishKey === '' || $secretKey === '') {
            $this->markTestSkipped(
                'Set CATEGORY_SUBSCRIBE_KEY, CATEGORY_PUBLISH_KEY and CATEGORY_SECRET_KEY to run the category '
                    . 'grant tests against a keyset with Get All enforcement enabled.'
            );
        }

        $config = $this->configuration(uniqid('php-sdk-category-admin-'));
        $config->setSecretKey($secretKey);
        $this->admin = new PubNub($config);
    }

    private function configuration(string $uuid): PNConfiguration
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey($this->subscribeKey);
        $config->setPublishKey($this->publishKey);
        $config->setUuid($uuid);
        // Ten seconds is the default and a loaded CI runner occasionally needs more than that.
        $config->setNonSubscribeRequestTimeout(30);

        return $config;
    }

    private function clientWithToken(string $uuid, string $token): PubNub
    {
        $client = new PubNub($this->configuration($uuid));
        $client->setToken($token);

        return $client;
    }

    private function clientWithAuthKey(string $authKey): PubNub
    {
        $config = $this->configuration(uniqid('php-sdk-category-client-'));
        $config->setAuthKey($authKey);

        return new PubNub($config);
    }

    /**
     * @param callable(GrantToken): void $configure
     */
    private function grantToken(string $uuid, callable $configure): string
    {
        $endpoint = $this->admin->grantToken()->ttl(10)->authorizedUuid($uuid);
        $configure($endpoint);

        $token = $endpoint->sync();
        $this->assertNotEmpty($token);

        return $token;
    }

    private function grantCategories(string $authKey, bool $get): PNAccessManagerAbstractResult
    {
        return $this->admin->grant()
            ->authKeys($authKey)
            ->categories([PNGrantCategory::CHANNELS, PNGrantCategory::UUIDS])
            ->get($get)
            ->ttl(10)
            ->sync();
    }

    private function assertListsChannelMetadata(
        PubNub $client,
        bool $expectAllowed,
        int $timeoutSeconds = self::PROPAGATION_TIMEOUT_SECONDS
    ): void {
        $this->assertListEventually(
            fn() => $client->getAllChannelMetadata()->limit('1')->sync(),
            $expectAllowed,
            'getAllChannelMetadata',
            $timeoutSeconds
        );
    }

    private function assertListsUuidMetadata(
        PubNub $client,
        bool $expectAllowed,
        int $timeoutSeconds = self::PROPAGATION_TIMEOUT_SECONDS
    ): void {
        $this->assertListEventually(
            fn() => $client->getAllUUIDMetadata()->limit('1')->sync(),
            $expectAllowed,
            'getAllUUIDMetadata',
            $timeoutSeconds
        );
    }

    /**
     * @param callable(): mixed $list
     */
    private function assertListEventually(
        callable $list,
        bool $expectAllowed,
        string $operation,
        int $timeoutSeconds
    ): void {
        $deadline = microtime(true) + $timeoutSeconds;

        while (true) {
            try {
                $list();
                $allowed = true;
            } catch (PubNubServerException $exception) {
                if ($exception->getStatusCode() !== 403) {
                    throw $exception;
                }
                $allowed = false;
            }

            if ($allowed === $expectAllowed) {
                $this->addToAssertionCount(1);
                return;
            }

            if (microtime(true) >= $deadline) {
                $this->fail(sprintf(
                    '%s should have been %s within %d seconds',
                    $operation,
                    $expectAllowed ? 'allowed' : 'denied with 403',
                    $timeoutSeconds
                ));
            }

            usleep(self::POLL_INTERVAL_MICROSECONDS);
        }
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

    public function testTokenWithOnlyResourceLevelGetCannotListAll(): void
    {
        $uuid = uniqid('php-sdk-category-client-');
        $token = $this->grantToken($uuid, function (GrantToken $grant) use ($uuid): void {
            $grant->addChannelResources([uniqid('php-sdk-category-channel-') => ['get' => true]])
                ->addUuidResources([$uuid => ['get' => true]]);
        });
        $client = $this->clientWithToken($uuid, $token);

        $this->assertListsChannelMetadata($client, false);
        $this->assertListsUuidMetadata($client, false);
    }

    public function testTokenWithBothCategoriesCanListAll(): void
    {
        $uuid = uniqid('php-sdk-category-client-');
        $token = $this->grantToken($uuid, function (GrantToken $grant): void {
            $grant->categories([
                PNGrantCategory::CHANNELS => ['get' => true],
                PNGrantCategory::UUIDS => ['get' => true],
            ]);
        });

        $parsed = $this->admin->parseToken($token);
        $this->assertTrue($this->granted($parsed->getChannelsCategory(), 'channels category')->hasGet());
        $this->assertTrue($this->granted($parsed->getUuidsCategory(), 'uuids category')->hasGet());

        $client = $this->clientWithToken($uuid, $token);
        $this->assertListsChannelMetadata($client, true);
        $this->assertListsUuidMetadata($client, true);
    }

    public function testTokenWithChannelsCategoryCanListOnlyChannelMetadata(): void
    {
        $uuid = uniqid('php-sdk-category-client-');
        $token = $this->grantToken($uuid, function (GrantToken $grant): void {
            $grant->categories([PNGrantCategory::CHANNELS => ['get' => true]]);
        });

        $parsed = $this->admin->parseToken($token);
        $this->assertTrue($this->granted($parsed->getChannelsCategory(), 'channels category')->hasGet());
        $this->assertFalse($parsed->getUuidsCategory());

        $client = $this->clientWithToken($uuid, $token);
        $this->assertListsChannelMetadata($client, true);
        $this->assertListsUuidMetadata($client, false);
    }

    public function testCategoryGrantToAnAuthKeyAllowsListingAll(): void
    {
        $authKey = uniqid('php-sdk-category-auth-');

        $result = $this->grantCategories($authKey, true);

        $this->assertSame('category', $result->getLevel());
        $this->assertEqualsCanonicalizing(['channels', 'uuids'], array_keys($result->getCategories()));
        foreach ($result->getCategories() as $name => $category) {
            $this->assertTrue($category->getAuthKeys()[$authKey]->isGetEnabled(), "$name: get should be granted");
        }

        $client = $this->clientWithAuthKey($authKey);
        $this->assertListsChannelMetadata($client, true);
        $this->assertListsUuidMetadata($client, true);
    }

    public function testOneMinuteCategoryGrantIsRejectedByTheServer(): void
    {
        try {
            $this->admin->grant()
                ->authKeys(uniqid('php-sdk-category-auth-'))
                ->categories(PNGrantCategory::CHANNELS)
                ->get(true)
                ->ttl(1)
                ->sync();
        } catch (PubNubServerException $exception) {
            $this->assertSame(400, $exception->getStatusCode());
            return;
        }

        $this->fail('A one-minute category grant should be rejected');
    }
}
