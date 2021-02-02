<?php

namespace Tests\Integrational\Objects\Channel;

use PubNubTestCase;


class GetAllChannelMetadataEndpointTest extends PubNubTestCase
{
    public function testGetAllChannelMetadata()
    {
        $this->pubnub_pam->setChannelMetadata()
            ->channel("ch")
            ->meta([
                "id" => "ch",
                "name" => "ch_name",
                "description" => "ch_description",
                "custom" => [
                    "a" => "aa",
                    "b" => "bb"
                ]
            ])
            ->sync();

        $this->pubnub_pam->setChannelMetadata()
            ->channel("ch1")
            ->meta([
                "id" => "ch1",
                "name" => "ch1_name",
                "description" => "ch1_description",
                "custom" => [
                    "a" => "aa1",
                    "b" => "bb1"
                ]
            ])
            ->sync();

        $this->pubnub_pam->setChannelMetadata()
            ->channel("ch2")
            ->meta([
                "id" => "ch2",
                "name" => "ch2_name",
                "description" => "ch2_description",
                "custom" => [
                    "a" => "aa2",
                    "b" => "bb2"
                ]
            ])
            ->sync();

        $response = $this->pubnub_pam->getAllChannelMetadata()
            ->includeFields([ "totalCount" => true, "customFields" => true ])
            ->sync();

        $this->assertNotEmpty($response);
        // $this->assertEquals(3, $response->getTotalCount());

        $data = $response->getData();

        // $this->assertEquals(3, count($data));

        $value = $data[0];
        $this->assertEquals("ch", $value->getId());
        $this->assertEquals("ch_name", $value->getName());
        $this->assertEquals("ch_description", $value->getDescription());
        $custom = $value->getCustom();
        $this->assertNotEmpty($custom);
        $this->assertEquals("aa", $custom->a);
        $this->assertEquals("bb", $custom->b);

        $value = $data[1];
        $this->assertEquals("ch1", $value->getId());
        $this->assertEquals("ch1_name", $value->getName());
        $this->assertEquals("ch1_description", $value->getDescription());
        $custom = $value->getCustom();
        $this->assertNotEmpty($custom);
        $this->assertEquals("aa1", $custom->a);
        $this->assertEquals("bb1", $custom->b);

        $value = $data[2];
        $this->assertEquals("ch2", $value->getId());
        $this->assertEquals("ch2_name", $value->getName());
        $this->assertEquals("ch2_description", $value->getDescription());
        $custom = $value->getCustom();
        $this->assertNotEmpty($custom);
        $this->assertEquals("aa2", $custom->a);
        $this->assertEquals("bb2", $custom->b);        
    }
}
