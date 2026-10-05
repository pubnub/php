<?php

namespace PubNubTests\functional\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Endpoints\Access\GrantToken;
use PubNub\Exceptions\PubNubValidationException;
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

    /**
     * General Access Manager surface rather than a DataSync scope, but it is here because DataSync
     * is what needs it: a User record is authorised through "users", and the SDK previously had no
     * way to write that key, only "uuids". The two are separate scopes on the wire.
     */
    public function testUserScopeIsSeparateFromTheUuidScope(): void
    {
        $body = $this->body(
            $this->pubnub->grantToken()
                ->ttl(60)
                ->addUserResources(['alice' => ['get' => true]])
                ->addUuidResources(['alice' => ['read' => true]])
                ->addUserPatterns(['^alice-.*$' => ['get' => true]])
        );

        $this->assertSame(32, $body['permissions']['resources']['users']['alice']);
        $this->assertSame(1, $body['permissions']['resources']['uuids']['alice']);
        $this->assertSame(32, $body['permissions']['patterns']['users']['^alice-.*$']);
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

    /**
     * User and Channel records take their permissions from the shared uuid and channel scopes,
     * but a projection still has to be assigned to them under their own family.
     */
    public function testProjectionsCoverThePredefinedFamiliesToo(): void
    {
        $body = $this->body(
            $this->pubnub->grantToken()
                ->ttl(60)
                ->addUuidResources(['user-1' => ['get' => true]])
                ->addChannelResources(['channel-1' => ['read' => true]])
                ->dataSyncProjections([
                    'resources' => [
                        'users' => ['user-1' => 'public'],
                        'channels' => ['channel-1' => '__default__'],
                        'relationships' => ['rel-1' => 'brief'],
                    ],
                ])
        );

        $projections = $body['permissions']['meta']['pn-projections'];

        $this->assertSame('public', $projections['res']['datasync:users:user-1']);
        $this->assertSame('__default__', $projections['res']['datasync:channels:channel-1']);
        $this->assertSame('brief', $projections['res']['datasync:relationships:rel-1']);
        $this->assertSame(32, $body['permissions']['resources']['uuids']['user-1']);
        $this->assertSame(1, $body['permissions']['resources']['channels']['channel-1']);
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

    /**
     * A projection restricts what the holder sees, so a key the builder does not recognise has to
     * be reported. Skipping it quietly would grant the token with the default view instead of the
     * intended one, which can be the wider of the two.
     *
     * @dataProvider malformedProjectionProvider
     * @param array<mixed, mixed> $projections
     */
    public function testMalformedProjectionsAreRejected(array $projections, string $expectedMessage): void
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->pubnub->grantToken()->dataSyncProjections($projections);
    }

    /**
     * @return array<string, array{0: array<mixed, mixed>, 1: string}>
     */
    public function malformedProjectionProvider(): array
    {
        return [
            'singular scope' => [
                ['resource' => ['entities' => ['vehicle-1' => 'public']]],
                'unknown projection scope "resource"',
            ],
            'singular pattern scope' => [
                ['pattern' => ['entities' => ['^vehicle-.*$' => 'public']]],
                'unknown projection scope "pattern"',
            ],
            'singular family' => [
                ['resources' => ['entity' => ['vehicle-1' => 'public']]],
                'unknown projection family "entity" under "resources"',
            ],
            'scope is not a map' => [
                ['resources' => 'entities'],
                'projection scope "resources" must be an array',
            ],
            'family is not a map' => [
                ['resources' => ['entities' => 'public']],
                '"resources.entities" must map an identifier to a projection name',
            ],
            'empty identifier' => [
                ['resources' => ['entities' => ['' => 'public']]],
                '"resources.entities" has an empty identifier',
            ],
            'empty projection name' => [
                ['resources' => ['entities' => ['vehicle-1' => '']]],
                'must be a non-empty string',
            ],
        ];
    }

    /**
     * An identifier that looks like a number arrives as an int array key, which must not be
     * mistaken for a malformed entry.
     */
    public function testANumericIdentifierIsAccepted(): void
    {
        $body = $this->body(
            $this->pubnub->grantToken()
                ->ttl(60)
                ->dataSyncProjections(['resources' => ['entities' => ['1234' => 'public']]])
        );

        $this->assertSame('public', $body['permissions']['meta']['pn-projections']['res']['datasync:entities:1234']);
    }

    public function testNoProjectionsMeansNoMetaKey(): void
    {
        $body = $this->body($this->pubnub->grantToken()->ttl(60));

        $this->assertArrayNotHasKey('meta', $body['permissions']);
    }
}
