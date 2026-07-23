<?php

namespace PubNubTests\integrational\objects\member;

use PubNubTestCase;
use PubNub\Models\Consumer\Objects\Member\PNChannelMember;
use PubNub\Models\Consumer\Objects\Member\PNMembersResult;
use PubNubTests\helpers\PsrStubClient;

/**
 * Integration tests for ManageMembers requests that carry only one operation
 * (set-only or remove-only), executed through the public sync() API against a
 * stubbed PSR-18 client.
 *
 * Remove-only requests became reachable once validateParams was fixed to use
 * `||` instead of `or`, but buildData still reads the $setUuids/$removeUuids
 * typed properties, which have no default value. Any request that does not
 * populate both a set* and a remove* operation therefore dies with
 * "Typed property ... must not be accessed before initialization".
 */
class ManageMembersSingleOperationTest extends PubNubTestCase
{
    private const CHANNEL = 'members-single-op';

    private PsrStubClient $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = new PsrStubClient();
        $this->pubnub_demo->setClient($this->client);
        $this->pubnub_demo->getConfiguration()->setUuid('sampleUUID');
        $this->stubManageMembersResponse();
    }

    private function stubManageMembersResponse(): void
    {
        $this->client->stubFor('/v2/objects/demo/channels/' . self::CHANNEL . '/uuids')
            ->withQuery([
                'pnsdk' => $this->pubnub_demo->getSdkFullName(),
                'uuid' => 'sampleUUID',
            ])
            ->setResponseBody('{"status": 200, "data": []}');
    }

    public function testRemoveMembersOnly(): void
    {
        $response = $this->pubnub_demo->manageMembers()
            ->channel(self::CHANNEL)
            ->removeMembers([new PNChannelMember('uuid1')])
            ->sync();

        $this->assertInstanceOf(PNMembersResult::class, $response);
    }

    public function testRemoveUuidsOnly(): void
    {
        $response = $this->pubnub_demo->manageMembers()
            ->channel(self::CHANNEL)
            ->removeUuids(['uuid1'])
            ->sync();

        $this->assertInstanceOf(PNMembersResult::class, $response);
    }

    public function testSetMembersOnly(): void
    {
        $response = $this->pubnub_demo->manageMembers()
            ->channel(self::CHANNEL)
            ->setMembers([new PNChannelMember('uuid1')])
            ->sync();

        $this->assertInstanceOf(PNMembersResult::class, $response);
    }

    public function testSetUuidsOnly(): void
    {
        $response = $this->pubnub_demo->manageMembers()
            ->channel(self::CHANNEL)
            ->setUuids(['uuid1'])
            ->sync();

        $this->assertInstanceOf(PNMembersResult::class, $response);
    }
}
