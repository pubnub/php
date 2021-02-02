<?php

namespace Tests\Functional\Objects\UUID;

use PubNub\Endpoints\Objects\UUID\GetAllUUIDMetadata;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class GetAllUUIDMetadataTest extends PubNubTestCase
{
    public function testGetAllUUIDMetadata()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getAllMetadata = new GetAllUUIDMetadataExposed($this->pubnub);

        $getAllMetadata
            ->includeFields([ "totalCount" => true, "customFields" => true ]);

        $this->assertEquals(sprintf(
            GetAllUUIDMetadata::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey()
        ), $getAllMetadata->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom",
            "count" => "true"
        ], $getAllMetadata->buildParams());

        $responseData = [
            "status" => 200,
            "data" => [
              [
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
            ],
            "totalCount" => 1,
            "next" => "MUIwQTAwMUItQkRBRC00NDkyLTgyMEMtODg2OUU1N0REMTNBCg==",
            "prev" => "M0FFODRENzMtNjY2Qy00RUExLTk4QzktNkY1Q0I2MUJFNDRCCg=="
        ];

        $decoded_data = $getAllMetadata->createResponse($responseData);

        $this->assertNotEmpty($decoded_data);
        $this->assertEquals(1, $decoded_data->getTotalCount());

        $data = $decoded_data->getData();

        $this->assertEquals(1, count($data));

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
    }
}

class GetAllUUIDMetadataExposed extends GetAllUUIDMetadata
{
    public function buildParams()
    {
        return parent::buildParams();
    }
    
    public function buildPath()
    {
        return parent::buildPath();
    }

    public function createResponse($json)
    {
        return parent::createResponse($json);
    }

}
