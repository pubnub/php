<?php

namespace Tests\Integrational\Objects\Memberships;

use PubNubTestCase;

class SetMembershipsEndpointTest extends PubNubTestCase
{
    public function testSetMembershipsForChannel()
    {
        $response = $this->pubnub_pam->setMemberships()
            ->uuid("uuid")
            ->channels(["ch", "ch1", "ch2"])
            ->custom([
                "a" => "aa",
                "b" => "bb",
            ])
            ->includeFields([
                "totalCount" => true,
                "customFields" => true,
                "customChannelFields" => true,
                "channelFields" => true
            ])
            ->sync();


            $this->assertNotEmpty($response);
            $data = $response->getData();
            $this->assertNotEmpty($data);
            $this->assertEquals(3, count($data));

            $this->assertNotEmpty($data[0]->getChannel());
            $this->assertEquals("ch", $data[0]->getChannel()->getId());
            $this->assertNotEmpty($data[0]->getCustom());
            $custom_data = $data[0]->getCustom();
            $this->assertEquals("aa", $custom_data->a);
            $this->assertEquals("bb", $custom_data->b);

            $this->assertNotEmpty($data[1]->getChannel());
            $this->assertEquals("ch1", $data[1]->getChannel()->getId());
            $this->assertNotEmpty($data[1]->getCustom());
            $custom_data = $data[1]->getCustom();
            $this->assertEquals("aa", $custom_data->a);
            $this->assertEquals("bb", $custom_data->b);

            $this->assertNotEmpty($data[2]->getChannel());
            $this->assertEquals("ch2", $data[2]->getChannel()->getId());
            $this->assertNotEmpty($data[2]->getCustom());
            $custom_data = $data[2]->getCustom();
            $this->assertEquals("aa", $custom_data->a);
            $this->assertEquals("bb", $custom_data->b);
    }
}
