<?php

namespace PubNubTests\unit\objects\membership;

use PHPUnit\Framework\TestCase;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Endpoints\Objects\Membership\ManageMemberships;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\Membership\PNChannelMembership;

/**
 * Regression tests for the memberships/channels validation in ManageMemberships.
 *
 * ManageMemberships has the exact `or` bug that was fixed in ManageMembers:
 * `$memberships = !empty($this->setMemberships) or !empty($this->removeMemberships)`
 * binds looser than `=`, so only the `set*` operand is assigned and the
 * `remove*` operand is silently discarded. As a result remove-only operations
 * wrongly fail validation, and mixed memberships+channels calls wrongly pass.
 * See src/PubNub/Endpoints/Objects/Membership/ManageMemberships.php.
 */
class ManageMembershipsValidationTest extends TestCase
{
    private PubNub $pubnub;

    public function setUp(): void
    {
        parent::setUp();
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setPublishKey('demo');
        $config->setUuid('validation-test-uuid');
        $this->pubnub = new PubNub($config);
    }

    private function invokeValidateParams(ManageMemberships $endpoint): void
    {
        $method = new \ReflectionMethod($endpoint, 'validateParams');
        $method->invoke($endpoint);
    }

    /**
     * @return array{set: array<mixed>, delete: array<mixed>}
     */
    private function invokeBuildData(ManageMemberships $endpoint): array
    {
        $method = new \ReflectionMethod($endpoint, 'buildData');
        return json_decode($method->invoke($endpoint), true);
    }

    public function testSetMembershipsOnlyPassesValidation(): void
    {
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1')
            ->setMemberships([new PNChannelMembership('ch1')]);

        $this->invokeValidateParams($endpoint);
        $this->assertTrue(true, 'set-only memberships must pass validation');
    }

    public function testRemoveMembershipsOnlyPassesValidation(): void
    {
        // Regression: with the `or` bug this throws "Memberships or a list of channels missing".
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1')
            ->removeMemberships([new PNChannelMembership('ch1')]);

        $this->invokeValidateParams($endpoint);
        $this->assertTrue(true, 'remove-only memberships must pass validation');
    }

    public function testRemoveChannelsOnlyPassesValidation(): void
    {
        // Regression: with the `or` bug this throws "Memberships or a list of channels missing".
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1')
            ->removeChannels(['ch1']);

        $this->invokeValidateParams($endpoint);
        $this->assertTrue(true, 'remove-only channels must pass validation');
    }

    public function testSetAndRemoveMembershipsTogetherPassesValidation(): void
    {
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1')
            ->setMemberships([new PNChannelMembership('ch1')])
            ->removeMemberships([new PNChannelMembership('ch2')]);

        $this->invokeValidateParams($endpoint);
        $this->assertTrue(true, 'set + remove on the memberships side must pass validation');
    }

    public function testMixingRemoveMembershipsAndSetChannelsThrows(): void
    {
        // Regression: with the `or` bug $memberships is computed as false, so this
        // mixed memberships+channels call slips through validation. It must be rejected.
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1')
            ->removeMemberships([new PNChannelMembership('ch1')])
            ->setChannels(['ch2']);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('Either memberships or channels should be provided');
        $this->invokeValidateParams($endpoint);
    }

    public function testMixingSetMembershipsAndRemoveChannelsThrows(): void
    {
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1')
            ->setMemberships([new PNChannelMembership('ch1')])
            ->removeChannels(['ch2']);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('Either memberships or channels should be provided');
        $this->invokeValidateParams($endpoint);
    }

    public function testNothingProvidedThrows(): void
    {
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1');

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('Memberships or a list of channels missing');
        $this->invokeValidateParams($endpoint);
    }

    public function testMissingUserIdThrows(): void
    {
        $endpoint = $this->pubnub->manageMemberships()
            ->setMemberships([new PNChannelMembership('ch1')]);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('uuid missing');
        $this->invokeValidateParams($endpoint);
    }

    /**
     * Requests that pass validation must also build a request body. Like
     * ManageMembers before the fix, the set and remove properties are typed
     * without defaults, so any single-operation call must not blow up in
     * buildData with "Typed property ... must not be accessed before
     * initialization".
     */
    public function testRemoveMembershipsOnlyBuildsData(): void
    {
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1')
            ->removeMemberships([new PNChannelMembership('ch1')]);

        $this->invokeValidateParams($endpoint);
        $data = $this->invokeBuildData($endpoint);

        $this->assertSame([], $data['set']);
        $this->assertSame([['channel' => ['id' => 'ch1']]], $data['delete']);
    }

    public function testRemoveChannelsOnlyBuildsData(): void
    {
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1')
            ->removeChannels(['ch1']);

        $this->invokeValidateParams($endpoint);
        $data = $this->invokeBuildData($endpoint);

        $this->assertSame([], $data['set']);
        $this->assertSame([['channel' => ['id' => 'ch1']]], $data['delete']);
    }

    public function testSetMembershipsOnlyBuildsData(): void
    {
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1')
            ->setMemberships([new PNChannelMembership('ch1')]);

        $this->invokeValidateParams($endpoint);
        $data = $this->invokeBuildData($endpoint);

        $this->assertSame([['channel' => ['id' => 'ch1']]], $data['set']);
        $this->assertSame([], $data['delete']);
    }

    public function testSetChannelsOnlyBuildsData(): void
    {
        $endpoint = $this->pubnub->manageMemberships()
            ->userId('u1')
            ->setChannels(['ch1']);

        $this->invokeValidateParams($endpoint);
        $data = $this->invokeBuildData($endpoint);

        $this->assertCount(1, $data['set']);
        $this->assertSame(['id' => 'ch1'], $data['set'][0]['channel']);
        $this->assertSame([], $data['delete']);
    }
}
