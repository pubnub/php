<?php

namespace PubNubTests\functional;

use PHPUnit\Framework\TestCase;
use PubNub\Endpoints\Access\GrantToken;
use PubNub\Enums\PNGrantCategory;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PNConfiguration;
use PubNub\PubNub;

/**
 * Category-level App Context permissions as they are serialised into a grant token request.
 */
class GrantTokenCategoriesTest extends TestCase
{
    private PubNub $pubnub;

    public function setUp(): void
    {
        parent::setUp();
        $config = new PNConfiguration();
        $config->setSubscribeKey('sub-key');
        $config->setPublishKey('pub-key');
        $config->setSecretKey('secret-key');
        $config->setUuid('grant-token-uuid');
        $this->pubnub = new PubNub($config);
    }

    /**
     * @return array<string, mixed>
     */
    private function body(GrantToken $endpoint): array
    {
        $decoded = json_decode((string) $endpoint->buildData(), true);
        $this->assertIsArray($decoded, 'buildData() should produce a JSON object');

        return (array) $decoded;
    }

    public function testCategoriesAreSentAsTheGetBit(): void
    {
        $body = $this->body(
            $this->pubnub->grantToken()
                ->ttl(60)
                ->categories([
                    PNGrantCategory::CHANNELS => ['get' => true],
                    PNGrantCategory::UUIDS => ['get' => true],
                ])
        );

        $this->assertSame(['channels' => 32, 'uuids' => 32], $body['permissions']['categories']);
    }

    public function testCategoriesAreSentAlongsideResources(): void
    {
        $body = $this->body(
            $this->pubnub->grantToken()
                ->ttl(60)
                ->addChannelResources(['ch-a' => ['read' => true]])
                ->categories([PNGrantCategory::CHANNELS => ['get' => true]])
        );

        $this->assertSame(1, $body['permissions']['resources']['channels']['ch-a']);
        $this->assertSame(['channels' => 32], $body['permissions']['categories']);
    }

    /**
     * @dataProvider categoriesWithoutGetProvider
     * @param array<string, array<string, bool>>|null $categories
     */
    public function testCategoriesWithoutGetAreOmitted(?array $categories): void
    {
        $endpoint = $this->pubnub->grantToken()->ttl(60)->addChannelResources(['ch-a' => ['read' => true]]);
        if ($categories !== null) {
            $endpoint->categories($categories);
        }

        $this->assertArrayNotHasKey('categories', $this->body($endpoint)['permissions']);
    }

    /**
     * @return array<string, array{0: array<string, array<string, bool>>|null}>
     */
    public function categoriesWithoutGetProvider(): array
    {
        return [
            'not called' => [null],
            'empty' => [[]],
            'get is false' => [[PNGrantCategory::CHANNELS => ['get' => false]]],
            'no permissions' => [[PNGrantCategory::UUIDS => []]],
        ];
    }

    public function testALaterCallReplacesEarlierCategories(): void
    {
        $body = $this->body(
            $this->pubnub->grantToken()
                ->ttl(60)
                ->categories([PNGrantCategory::CHANNELS => ['get' => true]])
                ->categories([PNGrantCategory::UUIDS => ['get' => true]])
        );

        $this->assertSame(['uuids' => 32], $body['permissions']['categories']);
    }

    /**
     * The server rejects the whole grant for either mistake, so it is reported before sending.
     *
     * @dataProvider malformedCategoriesProvider
     * @param array<mixed, mixed> $categories
     */
    public function testMalformedCategoriesAreRejected(array $categories, string $expectedMessage): void
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->pubnub->grantToken()->categories($categories);
    }

    /**
     * @return array<string, array{0: array<mixed, mixed>, 1: string}>
     */
    public function malformedCategoriesProvider(): array
    {
        return [
            'unknown category' => [
                ['users' => ['get' => true]],
                'unknown category "users", expected one of: channels, uuids',
            ],
            'singular category' => [
                ['channel' => ['get' => true]],
                'unknown category "channel"',
            ],
            'permissions are not a map' => [
                [PNGrantCategory::CHANNELS => true],
                'category "channels" must map a permission name to a flag',
            ],
            'permission other than get' => [
                [PNGrantCategory::CHANNELS => ['get' => true, 'read' => true]],
                'category "channels" accepts only the get permission, "read" given',
            ],
        ];
    }

    public function testARejectedCallLeavesEarlierCategoriesInPlace(): void
    {
        $endpoint = $this->pubnub->grantToken()->ttl(60)->categories([PNGrantCategory::CHANNELS => ['get' => true]]);

        try {
            $endpoint->categories(['users' => ['get' => true]]);
            $this->fail('An unknown category should be rejected');
        } catch (PubNubValidationException $exception) {
            // expected
        }

        $this->assertSame(['channels' => 32], $this->body($endpoint)['permissions']['categories']);
    }
}
