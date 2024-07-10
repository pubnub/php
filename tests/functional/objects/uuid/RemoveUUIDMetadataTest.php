<?php

namespace Tests\Functional\Objects\UUID;

use PubNub\Endpoints\Objects\UUID\RemoveUUIDMetadata;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class RemoveUUIDMetadataTest extends PubNubTestCase
{
    public function testGetaMetadataFromUUID()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $removeMetadata = new RemoveUUIDMetadataExposed($this->pubnub);

        $removeMetadata
            ->uuid("uuid");

        $this->assertEquals(sprintf(
            RemoveUUIDMetadataExposed::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "uuid"
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

// phpcs:ignore PSR1.Classes.ClassDeclaration
class RemoveUUIDMetadataExposed extends RemoveUUIDMetadata
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
