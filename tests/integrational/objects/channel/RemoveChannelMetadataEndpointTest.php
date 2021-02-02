<?php

namespace Tests\Integrational\Objects\Channel;

use PubNubTestCase;


class RemoveChannelMetadataEndpointTest extends PubNubTestCase
{
    public function testRemoveMetadataFromChannel()
    {
        $response = $this->pubnub_pam->setChannelMetadata()
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

        $response = $this->pubnub_pam->removeChannelMetadata()
            ->channel("ch")
            ->sync();

        $this->assertTrue($response);
    }
}
