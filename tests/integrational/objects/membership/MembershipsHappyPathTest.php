<?php

namespace PubNubTests\integrational\objects\member;

use PubNubTestCase;
use PubNub\Models\Consumer\Objects\Membership\PNChannelMembership;
use PubNub\Models\Consumer\Objects\Membership\PNMembershipIncludes;
use PubNub\Models\Consumer\Objects\Membership\PNMembershipsResult;
use PubNub\Models\Consumer\Objects\Membership\PNMembershipsResultItem;

class MembershipsHappyPathTest extends PubNubTestCase
{
    protected string $user = "FoodMonarch";
    protected string $channel1 = "ItalianFoodDiscussionBoard";
    protected string $channel2 = "BestDishUK";
    protected string $channel3 = "PierogiesForAll";

    public function testHappyPath(): void
    {
        // Cleanup
        $staleMemberships = [];
        $getStaleMemberships = $this->pubnub->getMemberships()->userId($this->user)->sync();
        foreach ($getStaleMemberships->getData() as $membership) {
            array_push($staleMemberships, new PNChannelMembership($membership->getChannel()->getId()));
        }
        if (!empty($staleMemberships)) {
            $cleanup = $this->pubnub->removeMemberships()->userId($this->user)->memberships($staleMemberships)->sync();
            $this->assertInstanceOf(PNMembershipsResult::class, $cleanup);
            $this->assertCount(0, $cleanup->getData());
        }
        sleep(1);

        $includes = new PNMembershipIncludes();
        $includes->channel()->channelId()->channelCustom()->channelType()->channelStatus()->custom()->status()->type();
        $addMembership = $this->pubnub->setMemberships()
            ->userId($this->user)
            ->memberships([
                new PNChannelMembership($this->channel1, ['BestDish' => 'Pizza'], 'Admin', 'Active'),
                new PNChannelMembership($this->channel2, ['BestDish' => 'Lasagna'], 'Guest', 'Away'),
            ])
            ->include($includes)
            ->sync();

        $this->checkResponse($addMembership);
        sleep(1);
        $getMembership = $this->pubnub->getMemberships()
            ->userId($this->user)
            ->include($includes)
            ->sync();
        $this->checkResponse($getMembership);

        $manageMembership = $this->pubnub->manageMemberships()
            ->userId($this->user)
            ->removeMemberships([
                new PNChannelMembership($this->channel1),
                new PNChannelMembership($this->channel2),
            ])
            ->setMemberships([
                new PNChannelMembership($this->channel3, ['BestDish' => 'Everything'], 'Moderator', 'Omnomnomnom'),
            ])
            ->include($includes)
            ->sync();

        $this->assertInstanceOf(PNMembershipsResult::class, $manageMembership);
        $memberships = $manageMembership->getData();
        $this->assertCount(1, $memberships);
        $pierogies = $memberships[0];
        $this->assertEquals($this->channel3, $pierogies->getChannel()->getId());
        $this->assertEquals('Everything', $pierogies->getCustom()->BestDish);
        $this->assertEquals('Omnomnomnom', $pierogies->getStatus());
        $this->assertEquals('Moderator', $pierogies->getType());

        $removeMembers = $this->pubnub->removeMemberships()
            ->userId($this->user)
            ->memberships([
                new PNChannelMembership($this->channel1),
                new PNChannelMembership($this->channel2),
                new PNChannelMembership($this->channel3),
            ])
            ->sync();

        $this->assertInstanceOf(PNMembershipsResult::class, $removeMembers);
        $this->assertCount(0, $removeMembers->getData());
    }

    private function checkResponse(PNMembershipsResult $response): void
    {
        $this->assertInstanceOf(PNMembershipsResult::class, $response);
        $memberships = $response->getData();
        $this->assertCount(2, $memberships);
        $ch2 = $memberships[0];
        $ch1 = $memberships[1];

        $this->assertInstanceOf(PNMembershipsResultItem::class, $ch1);
        $this->assertInstanceOf(PNMembershipsResultItem::class, $ch2);
        $this->assertEquals($this->channel1, $ch1->getChannel()->getId());
        $this->assertEquals($this->channel2, $ch2->getChannel()->getId());
        $this->assertEquals('Pizza', $ch1->getCustom()->BestDish);
        $this->assertEquals('Lasagna', $ch2->getCustom()->BestDish);
        $this->assertEquals('Active', $ch1->getStatus());
        $this->assertEquals('Away', $ch2->getStatus());
        $this->assertEquals('Admin', $ch1->getType());
        $this->assertEquals('Guest', $ch2->getType());
    }
}
