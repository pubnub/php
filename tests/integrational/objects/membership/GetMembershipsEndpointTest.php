<?php

namespace Tests\Integrational\Objects\Memberships;

use PubNubTestCase;

class GetMembershipsEndpointTest extends PubNubTestCase
{
    public function testGetMemberships()
    {
        $this->pubnub_pam->setMemberships()
            ->uuid("uuid")
            ->channels(["ch", "ch1", "ch2"])
            ->custom([
                "a" => "aa",
                "b" => "bb",
            ])
            ->sync();

        $response = $this->pubnub_pam->getMemberships()
            ->uuid("uuid")
            ->includeFields([
                "totalCount" => true,
                "customFields" => true,
                "customUUIDFields" => true,
                "UUIDFields" => true,
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
