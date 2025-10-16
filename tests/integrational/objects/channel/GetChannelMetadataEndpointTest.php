<?php

namespace Tests\Integrational\Objects\Channel;

use PubNubTestCase;

class GetChannelMetadataEndpointTest extends PubNubTestCase
{
    public function testGetMetadataFromChannel()
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

        $response = $this->pubnub_pam->getChannelMetadata()
            ->channel("ch")
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertEquals("ch", $response->getId());
        $this->assertEquals("ch_name", $response->getName());
        $this->assertEquals("ch_description", $response->getDescription());

        $custom = $response->getCustom();

        $this->assertNotEmpty($custom);
        $this->assertEquals("aa", $custom->a);
        $this->assertEquals("bb", $custom->b);
    }
}
