<?php

namespace Tests\Functional\Objects\Membership;

use PubNub\Endpoints\Objects\Membership\SetMemberships;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class SetMembershipsTest extends PubNubTestCase
{
    public function testAddMemberships()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $setMemberships = new SetMembershipsExposed($this->pubnub);

        $setMemberships
            ->uuid("uuid")
            ->channels(["ch", "ch1", "ch2"])
            ->custom(["a" => "aa", "b" => "bb"]);

        $this->assertEquals(sprintf(
            SetMembershipsExposed::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "uuid"
        ), $setMemberships->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
        ], $setMemberships->buildParams());

        $data = $setMemberships->buildData();

        $decoded_data = json_decode($data);

        $this->assertNotEmpty($decoded_data->set);
        $this->assertEquals(3, count($decoded_data->set));

        $this->assertNotEmpty($decoded_data->set[0]->channel);
        $this->assertEquals("ch", $decoded_data->set[0]->channel->id);
        $this->assertNotEmpty($decoded_data->set[0]->custom);
        $custom_data = $decoded_data->set[0]->custom;
        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);

        $this->assertNotEmpty($decoded_data->set[1]->channel);
        $this->assertEquals("ch1", $decoded_data->set[1]->channel->id);
        $this->assertNotEmpty($decoded_data->set[1]->custom);
        $custom_data = $decoded_data->set[1]->custom;
        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);

        $this->assertNotEmpty($decoded_data->set[2]->channel);
        $this->assertEquals("ch2", $decoded_data->set[2]->channel->id);
        $this->assertNotEmpty($decoded_data->set[2]->custom);
        $custom_data = $decoded_data->set[2]->custom;
        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class SetMembershipsExposed extends SetMemberships
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
