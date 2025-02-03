<?php

namespace PubNubTests\integrational\objects\member;

use PubNub\Models\Consumer\Objects\Channel\PNSetChannelMetadataResult;
use PubNubTestCase;
use PubNub\Models\Consumer\Objects\Member\PNChannelMember;
use PubNub\Models\Consumer\Objects\Member\PNMemberIncludes;
use PubNub\Models\Consumer\Objects\Member\PNMembersResult;
use PubNub\Models\Consumer\Objects\UUID\PNSetUUIDMetadataResult;
use PubNub\Models\Consumer\Objects\Member\PNMembersResultItem;

class MembersHappyPathTest extends PubNubTestCase
{
    protected string $channel = 'Foodies';
    protected string $userName1 = "FoodMonarch";
    protected string $userName2 = "EpicFeastTime";
    protected string $userName3 = "PiotrekOgi";

    public function testHappyPath(): void
    {
        // Cleanup
        $this->pubnub->removeMembers()
        ->channel($this->channel)
        ->members([
            new PNChannelMember($this->userName1),
            new PNChannelMember($this->userName2),
            new PNChannelMember($this->userName3),
        ])
        ->sync();

        $channelSetup = $this->pubnub->setChannelMetadata()
            ->channel($this->channel)
            ->meta([
                'name' => 'Foodies',
                'description' => 'Best ThouTuba Creators on the planet',
            ])
            ->sync();

        $this->assertInstanceOf(PNSetChannelMetadataResult::class, $channelSetup);

        $userSetup1 = $this->pubnub->setUUIDMetadata()
            ->uuid($this->userName1)
            ->meta([
                'name' => 'FoodMonarch',
                'description' => 'The Emperor of Foodies',
            ])
            ->sync();

        $this->assertInstanceOf(PNSetUUIDMetadataResult::class, $userSetup1);

        $userSetup2 = $this->pubnub->setUUIDMetadata()
            ->uuid($this->userName2)
            ->meta([
                'name' => 'EpicFeastTime',
                'description' => 'It is Time for an Epic Feast',
            ])
            ->sync();

        $this->assertInstanceOf(PNSetUUIDMetadataResult::class, $userSetup2);

        $includes = new PNMemberIncludes();
        $includes->user()->userId()->userCustom()->userType()->userStatus()->custom()->status()->type();

        $addMembers = $this->pubnub->setMembers()
            ->channel($this->channel)
            ->members([
                new PNChannelMember($this->userName1, ['BestDish' => 'Pizza'], 'Svensson', 'Active'),
                new PNChannelMember($this->userName2, ['BestDish' => 'Lasagna'], 'Baconstrips', 'Retired'),
            ])
            ->include($includes)
            ->sync();
        $this->checkResponse($addMembers);

        $getMembers = $this->pubnub->getMembers()
            ->channel($this->channel)
            ->include($includes)
            ->sync();
        $this->checkResponse($getMembers);

        $manageMembers = $this->pubnub->manageMembers()
            ->channel($this->channel)
            ->removeMembers([
                new PNChannelMember($this->userName1),
                new PNChannelMember($this->userName2),
            ])
            ->setMembers([
                new PNChannelMember($this->userName3, ['BestDish' => 'Everything'], 'Siemanko', 'StillKicking'),
            ])
            ->include($includes)
            ->sync();

        $this->assertInstanceOf(PNMembersResult::class, $manageMembers);
        $members = $manageMembers->getData();
        $this->assertCount(1, $members);
        $piotrek = $members[0];
        $this->assertEquals($this->userName3, $piotrek->getUser()->getId());
        $this->assertEquals('Everything', $piotrek->getCustom()->BestDish);
        $this->assertEquals('StillKicking', $piotrek->getStatus());
        $this->assertEquals('Siemanko', $piotrek->getType());

        $removeMembers = $this->pubnub->removeMembers()
            ->channel($this->channel)
            ->members([
                new PNChannelMember($this->userName3),
            ])
            ->include($includes)
            ->sync();

        $this->assertInstanceOf(PNMembersResult::class, $removeMembers);
        $this->assertCount(0, $removeMembers->getData());
    }

    private function checkResponse(PNMembersResult $response): void
    {
        $this->assertInstanceOf(PNMembersResult::class, $response);
        $members = $response->getData();
        $this->assertCount(2, $members);
        $epic = $members[0];
        $monarch = $members[1];

        $this->assertInstanceOf(PNMembersResultItem::class, $epic);
        $this->assertInstanceOf(PNMembersResultItem::class, $monarch);
        $this->assertEquals('FoodMonarch', $monarch->getUser()->getId());
        $this->assertEquals('EpicFeastTime', $epic->getUser()->getId());
        $this->assertEquals('Pizza', $monarch->getCustom()->BestDish);
        $this->assertEquals('Lasagna', $epic->getCustom()->BestDish);
        $this->assertEquals('Active', $monarch->getStatus());
        $this->assertEquals('Retired', $epic->getStatus());
        $this->assertEquals('Svensson', $monarch->getType());
        $this->assertEquals('Baconstrips', $epic->getType());
    }
}
