<?php

namespace Tests\Integrational\Objects\Members;

use PubNubTestCase;

class SetMembersEndpointTest extends PubNubTestCase
{
    public function testSetMembersForChannel()
    {
        $response = $this->pubnub_pam->setMembers()
            ->channel("ch")
            ->uuids(["uuid", "uuid1", "uuid2"])
            ->custom([
                "a" => "aa",
                "b" => "bb",
            ])
            ->includeFields([
                "totalCount" => true,
                "customFields" => true,
                "customChannelFields" => true,
                "channelFields" => true,
            ])
            ->sync();


            $this->assertNotEmpty($response);
            $data = $response->getData();
            $this->assertNotEmpty($data);
            $this->assertEquals(3, count($data));

            $this->assertNotEmpty($data[0]->getUUID());
            $this->assertEquals("uuid", $data[0]->getUUID()->getId());
            $this->assertNotEmpty($data[0]->getCustom());
            $custom_data = $data[0]->getCustom();
            $this->assertEquals("aa", $custom_data->a);
            $this->assertEquals("bb", $custom_data->b);

            $this->assertNotEmpty($data[1]->getUUID());
            $this->assertEquals("uuid1", $data[1]->getUUID()->getId());
            $this->assertNotEmpty($data[1]->getCustom());
            $custom_data = $data[1]->getCustom();
            $this->assertEquals("aa", $custom_data->a);
            $this->assertEquals("bb", $custom_data->b);

            $this->assertNotEmpty($data[2]->getUUID());
            $this->assertEquals("uuid2", $data[2]->getUUID()->getId());
            $this->assertNotEmpty($data[2]->getCustom());
            $custom_data = $data[2]->getCustom();
            $this->assertEquals("aa", $custom_data->a);
            $this->assertEquals("bb", $custom_data->b);
    }
}
