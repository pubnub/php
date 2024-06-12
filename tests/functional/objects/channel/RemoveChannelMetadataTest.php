<?php

namespace Tests\Functional\Objects\Channel;

use PubNub\Endpoints\Objects\Channel\RemoveChannelMetadata;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class RemoveChannelMetadataTest extends PubNubTestCase
{
    public function testGetaMetadataFromChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $removeMetadata = new RemoveChannelMetadataExposed($this->pubnub);

        $removeMetadata
            ->channel("ch");

        $this->assertEquals(sprintf(
            RemoveChannelMetadata::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "ch"
        ), $removeMetadata->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid()
        ], $removeMetadata->buildParams());

        $responseData = [
          "data" => []
        ];

        $decoded_data = $removeMetadata->createResponse($responseData);

        $this->assertTrue($decoded_data);
    }
}

class RemoveChannelMetadataExposed extends RemoveChannelMetadata
{
    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }

    public function createResponse($result)
    {
        return parent::createResponse($result);
    }

}
