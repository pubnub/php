<?php

namespace Tests\Integrational\Objects\UUID;

use PubNubTestCase;


class RemoveUUIDMetadataEndpointTest extends PubNubTestCase
{
    public function testRemoveMetadataFromUUID()
    {
        $response = $this->pubnub_pam->setUUIDMetadata()
            ->uuid("uuid")
            ->meta([
                "id" => "uuid",
                "name" => "uuid_name",
                "custom" => [
                    "a" => "aa",
                    "b" => "bb"
                ]
            ])
            ->sync();

        $response = $this->pubnub_pam->removeUUIDMetadata()
            ->uuid("uuid")
            ->sync();

        $this->assertTrue($response);
    }
}
