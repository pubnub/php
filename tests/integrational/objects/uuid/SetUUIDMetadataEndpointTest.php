<?php

namespace Tests\Integrational\Objects\UUID;

use PubNubTestCase;


class SetUUIDMetadataEndpointTest extends PubNubTestCase
{
    public function testAddMetadataToUUID()
    {
        $response = $this->pubnub_pam->setUUIDMetadata()
            ->uuid("uuid")
            ->meta([
                "id" => "uuid",
                "name" => "uuid_name",
                "externalId" => "uuid_external_id",
                "profileUrl" => "uuid_profile_url",
                "email" => "uuid_email",
                "custom" => [
                    "a" => "aa",
                    "b" => "bb"
                ]
            ])
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertEquals("uuid", $response->getId());
        $this->assertEquals("uuid_name", $response->getName());
        $this->assertEquals("uuid_external_id", $response->getExternalId());
        $this->assertEquals("uuid_profile_url", $response->getProfileUrl());
        $this->assertEquals("uuid_email", $response->getEmail());

        $custom = $response->getCustom();

        $this->assertNotEmpty($custom);
        $this->assertEquals("aa", $custom->a);
        $this->assertEquals("bb", $custom->b);
    }
}
