<?php

namespace Tests\Functional\Objects\Member;

use PubNub\Endpoints\Objects\Member\RemoveMembers;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class RemoveMembersTest extends PubNubTestCase
{
    public function testRemoveMembersFromChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $removeMembers = new RemoveMembersExposed($this->pubnub);

        $removeMembers
            ->channel("ch")
            ->uuids(["uuid", "uuid1", "uuid2"]);

        $this->assertEquals(sprintf(
            RemoveMembers::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "ch"
        ), $removeMembers->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
        ], $removeMembers->buildParams());

        $data = $removeMembers->buildData();

        $decoded_data = json_decode($data);
        
        $this->assertNotEmpty($decoded_data->delete);
        $this->assertEquals(3, count($decoded_data->delete));
        $this->assertNotEmpty($decoded_data->delete[0]->uuid);
        $this->assertEquals("uuid", $decoded_data->delete[0]->uuid->id);
        $this->assertNotEmpty($decoded_data->delete[1]->uuid);
        $this->assertEquals("uuid1", $decoded_data->delete[1]->uuid->id);
        $this->assertNotEmpty($decoded_data->delete[2]->uuid);
        $this->assertEquals("uuid2", $decoded_data->delete[2]->uuid->id);
    }
}

class RemoveMembersExposed extends RemoveMembers
{
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
