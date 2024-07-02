<?php

namespace Tests\Functional\Objects\Membership;

use PubNub\Endpoints\Objects\Membership\RemoveMemberships;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class RemoveMembershipsTest extends PubNubTestCase
{
    public function testRemoveMemberships()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $removeMemberships = new RemoveMembershipsExposed($this->pubnub);

        $removeMemberships
            ->uuid("uuid")
            ->channels(["ch", "ch1", "ch2"]);

        $this->assertEquals(sprintf(
            RemoveMembershipsExposed::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "uuid"
        ), $removeMemberships->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
        ], $removeMemberships->buildParams());

        $data = $removeMemberships->buildData();

        $decoded_data = json_decode($data);

        $this->assertNotEmpty($decoded_data->delete);
        $this->assertEquals(3, count($decoded_data->delete));
        $this->assertNotEmpty($decoded_data->delete[0]->channel);
        $this->assertEquals("ch", $decoded_data->delete[0]->channel->id);
        $this->assertNotEmpty($decoded_data->delete[1]->channel);
        $this->assertEquals("ch1", $decoded_data->delete[1]->channel->id);
        $this->assertNotEmpty($decoded_data->delete[2]->channel);
        $this->assertEquals("ch2", $decoded_data->delete[2]->channel->id);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class RemoveMembershipsExposed extends RemoveMemberships
{
    public const PATH = parent::PATH;
    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildData()
    {
        return parent::buildData();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }
}
