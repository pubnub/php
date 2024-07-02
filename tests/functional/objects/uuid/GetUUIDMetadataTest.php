<?php

namespace Tests\Functional\Objects\UUID;

use PubNub\Endpoints\Objects\UUID\GetUUIDMetadata;
use PubNub\Models\Consumer\Objects\UUID\PNGetUUIDMetadataResult;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class GetUUIDMetadataTest extends PubNubTestCase
{
    /**
     * Use PNUUIDMetadata class for metadata
     */
    public function testGetMetadataFromUUID()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getMetadata = new GetUUIDMetadataExposed($this->pubnub);

        $getMetadata
            ->uuid("uuid");

        $this->assertEquals(sprintf(
            GetUUIDMetadataExposed::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "uuid"
        ), $getMetadata->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom"
        ], $getMetadata->buildParams());

        $responseData = [
          "data" => [
            "id" => "uuid",
            "name" => "uuid_name",
            "externalId" => "uuid_external_id",
            "profileUrl" => "uuid_profile_url",
            "email" => "uuid_email",
            "custom" => [
              "a" => "aa",
              "b" => "bb"
            ],
            "updated" => "2019-02-20T23:11:20.893755",
            "eTag" => "RTc1NUQwNUItREMyNy00Q0YxLUJCNDItMEZDMTZDMzVCN0VGCg=="
          ]
        ];

        $decoded_data = $getMetadata->createResponse($responseData);

        $this->assertEquals("uuid", $decoded_data->getID());
        $this->assertEquals("uuid_name", $decoded_data->getName());
        $this->assertEquals("uuid_external_id", $decoded_data->getExternalId());
        $this->assertEquals("uuid_profile_url", $decoded_data->getProfileUrl());
        $this->assertEquals("uuid_email", $decoded_data->getEmail());

        $custom_data = $decoded_data->getCustom();

        $this->assertEquals("aa", $custom_data->a);
        $this->assertEquals("bb", $custom_data->b);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class GetUUIDMetadataExposed extends GetUUIDMetadata
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

    public function createResponse($result): PNGetUUIDMetadataResult
    {
        return parent::createResponse($result);
    }
}
