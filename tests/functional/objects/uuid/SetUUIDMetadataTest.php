<?php

namespace Tests\Functional\Objects\UUID;

use PubNub\Endpoints\Objects\UUID\SetUUIDMetadata;
use PubNub\Models\Consumer\Objects\UUID\PNUUIDMetadata;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;
use stdClass;

class SetUUIDMetadataTest extends PubNubTestCase
{
    /**
     * Use PNUUIDMetadata class for metadata
     */
    public function testAddCustomObjectMetadataToUUID()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $setMetadata = new SetUUIDMetadataExposed($this->pubnub);

        $metadata = new PNUUIDMetadata(
            "uuid",
            "uuid_name",
            "uuid_external_id",
            "uuid_profile_url",
            "uuid_email",
            array("a" => "aa", "b" => "bb")
        );

        $setMetadata
            ->uuid("uuid")
            ->meta($metadata);

        $this->assertEquals(sprintf(
            SetUUIDMetadata::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "uuid"
        ), $setMetadata->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom",
        ], $setMetadata->buildParams());

        $data = $setMetadata->buildData();

        $decoded_data = json_decode($data);

        $this->assertEquals("uuid", $decoded_data->id);
        $this->assertEquals("uuid_name", $decoded_data->name);
        $this->assertEquals("uuid_external_id", $decoded_data->externalId);
        $this->assertEquals("uuid_profile_url", $decoded_data->profileUrl);
        $this->assertEquals("uuid_email", $decoded_data->email);

        $custom_data = $decoded_data->custom;

        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);
    }

    /**
     * Use literal array for metadata and for custom object
     */
    public function testAddArrayMetadataToUUID()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $setMetadata = new SetUUIDMetadataExposed($this->pubnub);

        $metadata = [
            "id" => "uuid",
            "name" => "uuid_name",
            "externalId" => "uuid_external_id",
            "profileUrl" => "uuid_profile_url",
            "email" => "uuid_email",
            "custom" => [ "a" => "aa", "b" => "bb" ]
        ];

        $setMetadata
            ->uuid("uuid")
            ->meta($metadata);

        $this->assertEquals(sprintf(
            SetUUIDMetadata::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "uuid"
        ), $setMetadata->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom",
        ], $setMetadata->buildParams());

        $data = $setMetadata->buildData();

        $decoded_data = json_decode($data);

        $this->assertEquals("uuid", $decoded_data->id);
        $this->assertEquals("uuid_name", $decoded_data->name);
        $this->assertEquals("uuid_external_id", $decoded_data->externalId);
        $this->assertEquals("uuid_profile_url", $decoded_data->profileUrl);
        $this->assertEquals("uuid_email", $decoded_data->email);

        $custom_data = $decoded_data->custom;

        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);
    }

    /**
     * Use StdClass for metadata and for custom object
     */
    public function testAddStdClassMetadataToUUID()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $setMetadata = new SetUUIDMetadataExposed($this->pubnub);

        $metadata = new stdClass();
        
        $metadata->id = "uuid";
        $metadata->name = "uuid_name";
        $metadata->externalId = "uuid_external_id";
        $metadata->profileUrl = "uuid_profile_url";
        $metadata->email = "uuid_email";
        $metadata->custom = new stdClass();

        $metadata->custom->a = "aa";
        $metadata->custom->b = "bb";

        $setMetadata
            ->uuid("uuid")
            ->meta($metadata);

        $this->assertEquals(sprintf(
            SetUUIDMetadata::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "uuid"
        ), $setMetadata->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom",
        ], $setMetadata->buildParams());

        $data = $setMetadata->buildData();

        $decoded_data = json_decode($data);

        $this->assertEquals("uuid", $decoded_data->id);
        $this->assertEquals("uuid_name", $decoded_data->name);
        $this->assertEquals("uuid_external_id", $decoded_data->externalId);
        $this->assertEquals("uuid_profile_url", $decoded_data->profileUrl);
        $this->assertEquals("uuid_email", $decoded_data->email);

        $custom_data = $decoded_data->custom;

        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);
    }
}

class SetUUIDMetadataExposed extends SetUUIDMetadata
{
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
