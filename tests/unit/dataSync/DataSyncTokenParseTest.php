<?php

namespace PubNubTests\unit\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Models\Access\Permissions;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerTokenResult;
use PubNub\Models\Consumer\AccessManager\PNDataSyncProjectionScope;
use PubNub\Models\Consumer\AccessManager\PNDataSyncProjections;

/**
 * Reading DataSync scopes and field projections back out of a parsed grant token.
 *
 * The fixtures mirror the CBOR-decoded shape that GrantToken::parseToken() hands to
 * PNAccessManagerTokenResult, so they double as a round-trip check against the wire format
 * asserted in the functional grant token test.
 */
class DataSyncTokenParseTest extends TestCase
{
    /**
     * @param array<string, mixed> $overrides
     */
    private function token(array $overrides = []): PNAccessManagerTokenResult
    {
        return PNAccessManagerTokenResult::fromArray(array_merge([
            'v' => 2,
            't' => 1755000000,
            'ttl' => 60,
            'res' => [
                'chan' => [],
                'grp' => [],
                'usr' => [],
                'spc' => [],
                'uuid' => [],
                'datasync:entities' => ['vehicle-1' => 65],
                'datasync:relationships' => ['rel-1' => 1],
                'datasync:memberships' => ['mem-1' => 9],
            ],
            'pat' => [
                'chan' => [],
                'grp' => [],
                'usr' => [],
                'spc' => [],
                'uuid' => [],
                'datasync:entities' => ['^vehicle-.*$' => 1],
                'datasync:relationships' => [],
                'datasync:memberships' => [],
            ],
            'meta' => [],
            'uuid' => 'my-uuid',
            'sig' => 'signature-bytes',
        ], $overrides));
    }

    /**
     * @param Permissions|false $granted
     */
    private function granted($granted): Permissions
    {
        $this->assertInstanceOf(Permissions::class, $granted);

        /** @var Permissions $granted */
        return $granted;
    }

    private function projections(PNAccessManagerTokenResult $token): PNDataSyncProjections
    {
        $projections = $token->getDataSyncProjections();
        $this->assertNotNull($projections);

        /** @var PNDataSyncProjections $projections */
        return $projections;
    }

    public function testDataSyncResourcePermissionsAreDecoded(): void
    {
        $token = $this->token();

        $entity = $this->granted($token->getDataSyncEntityResource('vehicle-1'));
        $this->assertTrue($entity->hasRead());
        $this->assertTrue($entity->hasUpdate());
        $this->assertFalse($entity->hasDelete());
        $this->assertFalse($entity->hasWrite());

        $this->assertTrue($this->granted($token->getDataSyncRelationshipResource('rel-1'))->hasRead());

        $membership = $this->granted($token->getDataSyncMembershipResource('mem-1'));
        $this->assertTrue($membership->hasRead());
        $this->assertTrue($membership->hasDelete());
    }

    public function testDataSyncPatternPermissionsAreDecoded(): void
    {
        $pattern = $this->granted($this->token()->getDataSyncEntityPattern('^vehicle-.*$'));

        $this->assertTrue($pattern->hasRead());
    }

    public function testUnknownDataSyncResourceReturnsFalse(): void
    {
        $token = $this->token();

        $this->assertFalse($token->getDataSyncEntityResource('vehicle-2'));
        $this->assertFalse($token->getDataSyncRelationshipPattern('^rel-.*$'));
    }

    public function testProjectionsAreSplitBackIntoResourceFamilies(): void
    {
        $token = $this->token([
            'meta' => [
                'pn-projections' => [
                    'res' => [
                        'datasync:entities:vehicle-1' => '__default__',
                        'datasync:relationships:rel-1' => 'brief',
                        'datasync:memberships:mem-1' => 'summary',
                    ],
                    'pat' => [
                        'datasync:entities:^vehicle-.*$' => 'public',
                    ],
                ],
            ],
        ]);

        $projections = $this->projections($token);

        $resources = $projections->getResources();
        $this->assertSame(['vehicle-1' => '__default__'], $resources->getEntities());
        $this->assertSame(['rel-1' => 'brief'], $resources->getRelationships());
        $this->assertSame(['mem-1' => 'summary'], $resources->getMemberships());
        $this->assertSame('__default__', $resources->getEntityProjection('vehicle-1'));

        $patterns = $projections->getPatterns();
        $this->assertSame('public', $patterns->getEntityProjection('^vehicle-.*$'));
        $this->assertSame([], $patterns->getMemberships());
    }

    public function testProjectionIdentifiersMayContainColons(): void
    {
        $scope = PNDataSyncProjectionScope::fromArray([
            'datasync:memberships:user:U1:channel:C1' => 'summary',
        ]);

        $this->assertSame('summary', $scope->getMembershipProjection('user:U1:channel:C1'));
    }

    public function testMalformedAndUnknownProjectionKeysAreSkipped(): void
    {
        $scope = PNDataSyncProjectionScope::fromArray([
            'datasync:entities:vehicle-1' => '__default__',
            'datasync:entities:' => 'no-identifier',
            'datasync:vehicle-1' => 'no-type',
            'datasync:widgets:widget-1' => 'unknown-family',
            'chan:my-channel' => 'not-datasync',
        ]);

        $this->assertSame(['vehicle-1' => '__default__'], $scope->getEntities());
        $this->assertSame([], $scope->getRelationships());
        $this->assertSame([], $scope->getMemberships());
    }

    public function testTokenWithoutProjectionsReturnsNull(): void
    {
        $this->assertNull($this->token()->getDataSyncProjections());
    }

    public function testProjectionScopeMissingFromMetaIsEmptyRatherThanNull(): void
    {
        $projections = $this->projections($this->token([
            'meta' => [
                'pn-projections' => [
                    'res' => ['datasync:entities:vehicle-1' => '__default__'],
                ],
            ],
        ]));

        $this->assertFalse($projections->getResources()->isEmpty());
        $this->assertTrue($projections->getPatterns()->isEmpty());
    }

    public function testProjectionsAppearInToArrayOnlyWhenPresent(): void
    {
        $this->assertArrayNotHasKey('projections', $this->token()->toArray());

        $withProjections = $this->token([
            'meta' => [
                'pn-projections' => [
                    'res' => ['datasync:entities:vehicle-1' => '__default__'],
                ],
            ],
        ])->toArray();

        $this->assertSame(
            ['vehicle-1' => '__default__'],
            $withProjections['projections']['resources']['entities']
        );
    }

    public function testDataSyncScopesSurviveToArray(): void
    {
        $resources = $this->token()->toArray()['resources'];

        $this->assertTrue($resources['datasync:entities']['vehicle-1']['read']);
        $this->assertTrue($resources['datasync:entities']['vehicle-1']['update']);
        $this->assertFalse($resources['datasync:entities']['vehicle-1']['join']);
    }
}
