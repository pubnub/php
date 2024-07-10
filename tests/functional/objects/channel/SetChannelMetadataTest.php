<?php

namespace Tests\Functional\Objects\Channel;

use PubNub\Endpoints\Objects\Channel\SetChannelMetadata;
use PubNub\Models\Consumer\Objects\Channel\PNChannelMetadata;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;
use stdClass;

class SetChannelMetadataTest extends PubNubTestCase
{
    /**
     * Use PNChannelMetadata class for metadata
     */
    public function testAddCustomObjectMetadataToChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $setMetadata = new SetChannelMetadataExposed($this->pubnub);

        $metadata = new PNChannelMetadata(
            "ch",
            "ch_name",
            "ch_description",
            array("a" => "aa", "b" => "bb")
        );

        $setMetadata
            ->channel("ch")
            ->meta($metadata);

        $this->assertEquals(sprintf(
            SetChannelMetadataExposed::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "ch"
        ), $setMetadata->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom",
        ], $setMetadata->buildParams());

        $data = $setMetadata->buildData();

        $decoded_data = json_decode($data);

        $this->assertEquals("ch", $decoded_data->id);
        $this->assertEquals("ch_name", $decoded_data->name);
        $this->assertEquals("ch_description", $decoded_data->description);

        $custom_data = $decoded_data->custom;

        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);
    }

    /**
     * Use literal array for metadata and for custom object
     */
    public function testAddArrayMetadataToChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $setMetadata = new SetChannelMetadataExposed($this->pubnub);

        $metadata = [
            "id" => "ch",
            "name" => "ch_name",
            "description" => "ch_description",
            "custom" => [ "a" => "aa", "b" => "bb" ]
        ];

        $setMetadata
            ->channel("ch")
            ->meta($metadata);

        $this->assertEquals(sprintf(
            SetChannelMetadataExposed::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "ch"
        ), $setMetadata->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom",
        ], $setMetadata->buildParams());

        $data = $setMetadata->buildData();

        $decoded_data = json_decode($data);

        $this->assertEquals("ch", $decoded_data->id);
        $this->assertEquals("ch_name", $decoded_data->name);
        $this->assertEquals("ch_description", $decoded_data->description);

        $custom_data = $decoded_data->custom;

        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);
    }

    /**
     * Use StdClass for metadata and for custom object
     */
    public function testAddStdClassMetadataToChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $setMetadata = new SetChannelMetadataExposed($this->pubnub);

        $metadata = new stdClass();

        $metadata->id = "ch";
        $metadata->name = "ch_name";
        $metadata->description = "ch_description";
        $metadata->custom = new stdClass();

        $metadata->custom->a = "aa";
        $metadata->custom->b = "bb";

        $setMetadata
            ->channel("ch")
            ->meta($metadata);

        $this->assertEquals(sprintf(
            SetChannelMetadataExposed::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "ch"
        ), $setMetadata->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom",
        ], $setMetadata->buildParams());

        $data = $setMetadata->buildData();

        $decoded_data = json_decode($data);

        $this->assertEquals("ch", $decoded_data->id);
        $this->assertEquals("ch_name", $decoded_data->name);
        $this->assertEquals("ch_description", $decoded_data->description);

        $custom_data = $decoded_data->custom;

        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class SetChannelMetadataExposed extends SetChannelMetadata
{
    public const PATH = parent::PATH;
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
