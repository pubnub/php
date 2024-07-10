<?php

namespace Tests\Functional\Objects\Channel;

use PubNub\Endpoints\Objects\Channel\GetChannelMetadata;
use PubNub\Models\Consumer\Objects\Channel\PNGetChannelMetadataResult;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class GetChannelMetadataTest extends PubNubTestCase
{
    /**
     * Use PNChannelMetadata class for metadata
     */
    public function testGetMetadataFromChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getMetadata = new GetChannelMetadataExposed($this->pubnub);

        $getMetadata
            ->channel("ch");

        $this->assertEquals(sprintf(
            GetChannelMetadataExposed::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "ch"
        ), $getMetadata->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom"
        ], $getMetadata->buildParams());

        $responseData = [
          "data" => [
            "id" => "ch",
            "name" => "ch_name",
            "description" => "ch_description",
            "custom" => [
              "a" => "aa",
              "b" => "bb"
            ],
            "updated" => "2019-02-20T23:11:20.893755",
            "eTag" => "RTc1NUQwNUItREMyNy00Q0YxLUJCNDItMEZDMTZDMzVCN0VGCg=="
          ]
        ];

        $decoded_data = $getMetadata->createResponse($responseData);

        $this->assertEquals("ch", $decoded_data->getID());
        $this->assertEquals("ch_name", $decoded_data->getName());
        $this->assertEquals("ch_description", $decoded_data->getDescription());

        $custom_data = $decoded_data->getCustom();

        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class GetChannelMetadataExposed extends GetChannelMetadata
{
    public const PATH = parent::PATH;

    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }

    public function createResponse($result): PNGetChannelMetadataResult
    {
        return parent::createResponse($result);
    }
}
