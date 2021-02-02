<?php

namespace Tests\Integrational\Objects\UUID;

use PubNubTestCase;


class GetAllUUIDMetadataEndpointTest extends PubNubTestCase
{
    public function testGetAllUUIDMetadata()
    {
        $this->pubnub_pam->setUUIDMetadata()
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

        $this->pubnub_pam->setUUIDMetadata()
            ->uuid("uuid1")
            ->meta([
                "id" => "uuid1",
                "name" => "uuid1_name",
                "externalId" => "uuid1_external_id",
                "profileUrl" => "uuid1_profile_url",
                "email" => "uuid1_email",
                "custom" => [
                    "a" => "aa1",
                    "b" => "bb1"
                ]
            ])
            ->sync();

        $this->pubnub_pam->setUUIDMetadata()
            ->uuid("uuid2")
            ->meta([
                "id" => "uuid2",
                "name" => "uuid2_name",
                "externalId" => "uuid2_external_id",
                "profileUrl" => "uuid2_profile_url",
                "email" => "uuid2_email",
                "custom" => [
                    "a" => "aa2",
                    "b" => "bb2"
                ]
            ])
            ->sync();

        $response = $this->pubnub_pam->getAllUUIDMetadata()
            ->includeFields([ "totalCount" => true, "customFields" => true ])
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertEquals(3, $response->getTotalCount());

        $data = $response->getData();

        $this->assertEquals(3, count($data));

        $value = $data[0];
        $this->assertEquals("uuid", $value->getId());
        $this->assertEquals("uuid_name", $value->getName());
        $this->assertEquals("uuid_external_id", $value->getExternalId());
        $this->assertEquals("uuid_profile_url", $value->getProfileUrl());
        $this->assertEquals("uuid_email", $value->getEmail());
        $custom = $value->getCustom();
        $this->assertNotEmpty($custom);
        $this->assertEquals("aa", $custom->a);
        $this->assertEquals("bb", $custom->b);

        $value = $data[1];
        $this->assertEquals("uuid1", $value->getId());
        $this->assertEquals("uuid1_name", $value->getName());
        $this->assertEquals("uuid1_external_id", $value->getExternalId());
        $this->assertEquals("uuid1_profile_url", $value->getProfileUrl());
        $this->assertEquals("uuid1_email", $value->getEmail());
        $custom = $value->getCustom();
        $this->assertNotEmpty($custom);
        $this->assertEquals("aa1", $custom->a);
        $this->assertEquals("bb1", $custom->b);  

        $value = $data[2];
        $this->assertEquals("uuid2", $value->getId());
        $this->assertEquals("uuid2_name", $value->getName());
        $this->assertEquals("uuid2_external_id", $value->getExternalId());
        $this->assertEquals("uuid2_profile_url", $value->getProfileUrl());
        $this->assertEquals("uuid2_email", $value->getEmail());
        $custom = $value->getCustom();
        $this->assertNotEmpty($custom);
        $this->assertEquals("aa2", $custom->a);
        $this->assertEquals("bb2", $custom->b);        
    }
}
