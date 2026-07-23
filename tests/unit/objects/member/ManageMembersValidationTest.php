<?php

namespace PubNubTests\unit\objects\member;

use PHPUnit\Framework\TestCase;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Endpoints\Objects\Member\ManageMembers;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\Member\PNChannelMember;

/**
 * Regression tests for the members/uuids validation in ManageMembers.
 *
 * These pin the behaviour of the `||` fix. Previously the endpoint used `or`,
 * which binds looser than `=`, so `$members`/`$uuids` were assigned only the
 * `set*` operand and the `remove*` operand was silently discarded. As a result
 * remove-only operations wrongly failed validation, and mixed members+uuids
 * calls wrongly passed. See src/PubNub/Endpoints/Objects/Member/ManageMembers.php.
 */
class ManageMembersValidationTest extends TestCase
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

    private function invokeValidateParams(ManageMembers $endpoint): void
    {
        $method = new \ReflectionMethod($endpoint, 'validateParams');
        $method->invoke($endpoint);
    }

    /**
     * @return array{set: array<mixed>, delete: array<mixed>}
     */
    private function invokeBuildData(ManageMembers $endpoint): array
    {
        $method = new \ReflectionMethod($endpoint, 'buildData');
        return json_decode($method->invoke($endpoint), true);
    }

    public function testSetMembersOnlyPassesValidation(): void
    {
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch')
            ->setMembers([new PNChannelMember('u1')]);

        $this->invokeValidateParams($endpoint);
        $this->assertTrue(true, 'set-only members must pass validation');
    }

    public function testRemoveMembersOnlyPassesValidation(): void
    {
        // Regression: with the old `or` bug this threw "Members or a list of uuids missing".
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch')
            ->removeMembers([new PNChannelMember('u1')]);

        $this->invokeValidateParams($endpoint);
        $this->assertTrue(true, 'remove-only members must pass validation');
    }

    public function testRemoveUuidsOnlyPassesValidation(): void
    {
        // Regression: with the old `or` bug this threw "Members or a list of uuids missing".
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch')
            ->removeUuids(['u1']);

        $this->invokeValidateParams($endpoint);
        $this->assertTrue(true, 'remove-only uuids must pass validation');
    }

    public function testSetAndRemoveMembersTogetherPassesValidation(): void
    {
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch')
            ->setMembers([new PNChannelMember('u1')])
            ->removeMembers([new PNChannelMember('u2')]);

        $this->invokeValidateParams($endpoint);
        $this->assertTrue(true, 'set + remove on the members side must pass validation');
    }

    public function testMixingRemoveMembersAndSetUuidsThrows(): void
    {
        // Regression: with the old `or` bug $members was computed as false, so this
        // mixed members+uuids call slipped through validation. It must now be rejected.
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch')
            ->removeMembers([new PNChannelMember('u1')])
            ->setUuids(['u2']);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('Either members or uuids should be provided');
        $this->invokeValidateParams($endpoint);
    }

    public function testMixingSetMembersAndRemoveUuidsThrows(): void
    {
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch')
            ->setMembers([new PNChannelMember('u1')])
            ->removeUuids(['u2']);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('Either members or uuids should be provided');
        $this->invokeValidateParams($endpoint);
    }

    public function testNothingProvidedThrows(): void
    {
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch');

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('Members or a list of uuids missing');
        $this->invokeValidateParams($endpoint);
    }

    /**
     * Requests that pass validation must also build a request body. The set*
     * and remove* properties are typed without defaults, so any single-operation
     * call (e.g. remove-only, which validation now correctly allows) must not
     * blow up in buildData with "Typed property ... must not be accessed before
     * initialization".
     */
    public function testRemoveMembersOnlyBuildsData(): void
    {
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch')
            ->removeMembers([new PNChannelMember('u1')]);

        $this->invokeValidateParams($endpoint);
        $data = $this->invokeBuildData($endpoint);

        $this->assertSame([], $data['set']);
        $this->assertSame([['uuid' => ['id' => 'u1']]], $data['delete']);
    }

    public function testRemoveUuidsOnlyBuildsData(): void
    {
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch')
            ->removeUuids(['u1']);

        $this->invokeValidateParams($endpoint);
        $data = $this->invokeBuildData($endpoint);

        $this->assertSame([], $data['set']);
        $this->assertSame([['uuid' => ['id' => 'u1']]], $data['delete']);
    }

    public function testSetMembersOnlyBuildsData(): void
    {
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch')
            ->setMembers([new PNChannelMember('u1')]);

        $this->invokeValidateParams($endpoint);
        $data = $this->invokeBuildData($endpoint);

        $this->assertSame([['uuid' => ['id' => 'u1']]], $data['set']);
        $this->assertSame([], $data['delete']);
    }

    public function testSetUuidsOnlyBuildsData(): void
    {
        $endpoint = $this->pubnub->manageMembers()
            ->channel('ch')
            ->setUuids(['u1']);

        $this->invokeValidateParams($endpoint);
        $data = $this->invokeBuildData($endpoint);

        $this->assertSame([['uuid' => ['id' => 'u1']]], $data['set']);
        $this->assertSame([], $data['delete']);
    }

    public function testMissingChannelThrows(): void
    {
        $endpoint = $this->pubnub->manageMembers()
            ->setMembers([new PNChannelMember('u1')]);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('channel missing');
        $this->invokeValidateParams($endpoint);
    }
}
