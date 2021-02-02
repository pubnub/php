<?php

namespace Tests\Functional\Objects\Member;

use PubNub\Endpoints\Objects\Member\SetMembers;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class SetMembersTest extends PubNubTestCase
{
    public function testAddMembersToChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $setMembers = new SetMembersExposed($this->pubnub);

        $setMembers
            ->channel("ch")
            ->uuids(["uuid", "uuid1", "uuid2"])
            ->custom(["a" => "aa", "b" => "bb"]);

        $this->assertEquals(sprintf(
            SetMembers::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "ch"
        ), $setMembers->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
        ], $setMembers->buildParams());

        $data = $setMembers->buildData();

        $decoded_data = json_decode($data);
        
        $this->assertNotEmpty($decoded_data->set);
        $this->assertEquals(3, count($decoded_data->set));

        $this->assertNotEmpty($decoded_data->set[0]->uuid);
        $this->assertEquals("uuid", $decoded_data->set[0]->uuid->id);
        $this->assertNotEmpty($decoded_data->set[0]->custom);
        $custom_data = $decoded_data->set[0]->custom;
        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);

        $this->assertNotEmpty($decoded_data->set[1]->uuid);
        $this->assertEquals("uuid1", $decoded_data->set[1]->uuid->id);
        $this->assertNotEmpty($decoded_data->set[1]->custom);
        $custom_data = $decoded_data->set[1]->custom;
        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);

        $this->assertNotEmpty($decoded_data->set[2]->uuid);
        $this->assertEquals("uuid2", $decoded_data->set[2]->uuid->id);
        $this->assertNotEmpty($decoded_data->set[2]->custom);
        $custom_data = $decoded_data->set[2]->custom;
        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);
    }
}

class SetMembersExposed extends SetMembers
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
