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
            RemoveChannelMetadataExposed::PATH,
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

//phpcs:ignore PSR1.Classes.ClassDeclaration
class RemoveChannelMetadataExposed extends RemoveChannelMetadata
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

    public function createResponse($result): bool
    {
        return parent::createResponse($result);
    }
}
