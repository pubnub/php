<?php

namespace PubNubTests\functional\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Endpoints\Access\GrantToken;
use PubNub\PNConfiguration;
use PubNub\PubNub;

/**
 * DataSync scopes and field projections as they are serialised into a grant token request.
 */
class DataSyncGrantTokenTest extends TestCase
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

    public function testDataSyncResourcesUseTheirOwnScopeKeys(): void
    {
        $body = $this->body(
            $this->pubnub->grantToken()
                ->ttl(60)
                ->addDataSyncEntityResources(['vehicle-1' => ['read' => true, 'update' => true]])
                ->addDataSyncRelationshipResources(['rel-1' => ['read' => true]])
                ->addDataSyncMembershipResources(['mem-1' => ['read' => true, 'delete' => true]])
        );

        $resources = $body['permissions']['resources'];

        $this->assertSame(65, $resources['datasync:entities']['vehicle-1']);
        $this->assertSame(1, $resources['datasync:relationships']['rel-1']);
        $this->assertSame(9, $resources['datasync:memberships']['mem-1']);
    }

    public function testDataSyncPatternsUseTheirOwnScopeKeys(): void
    {
        $body = $this->body(
            $this->pubnub->grantToken()
                ->ttl(60)
                ->addDataSyncEntityPatterns(['^vehicle-.*$' => ['read' => true]])
        );

        $this->assertSame(1, $body['permissions']['patterns']['datasync:entities']['^vehicle-.*$']);
    }

    public function testProjectionsAreFlattenedIntoMeta(): void
    {
        $body = $this->body(
            $this->pubnub->grantToken()
                ->ttl(60)
                ->dataSyncProjections([
                    'resources' => [
                        'entities' => ['vehicle-1' => '__default__'],
                        'memberships' => ['mem-1' => 'summary'],
                    ],
                    'patterns' => [
                        'entities' => ['^vehicle-.*$' => 'public'],
                    ],
                ])
        );

        $projections = $body['permissions']['meta']['pn-projections'];

        $this->assertSame('__default__', $projections['res']['datasync:entities:vehicle-1']);
        $this->assertSame('summary', $projections['res']['datasync:memberships:mem-1']);
        $this->assertSame('public', $projections['pat']['datasync:entities:^vehicle-.*$']);
    }

    public function testProjectionsDoNotClobberUserSuppliedMeta(): void
    {
        $body = $this->body(
            $this->pubnub->grantToken()
                ->ttl(60)
                ->meta(['tenant' => 'acme'])
                ->dataSyncProjections([
                    'resources' => ['entities' => ['vehicle-1' => '__default__']],
                ])
        );

        $meta = $body['permissions']['meta'];

        $this->assertSame('acme', $meta['tenant']);
        $this->assertArrayHasKey('pn-projections', $meta);
    }

    public function testNoProjectionsMeansNoMetaKey(): void
    {
        $body = $this->body($this->pubnub->grantToken()->ttl(60));

        $this->assertArrayNotHasKey('meta', $body['permissions']);
    }
}
