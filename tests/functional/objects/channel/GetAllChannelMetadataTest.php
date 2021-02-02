<?php

namespace Tests\Functional\Objects\Channel;

use PubNub\Endpoints\Objects\Channel\GetAllChannelMetadata;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class GetAllChannelMetadataTest extends PubNubTestCase
{
    public function testGetAllChannelMetadata()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getAllMetadata = new GetAllChannelMetadataExposed($this->pubnub);

        $getAllMetadata
            ->includeFields([ "totalCount" => true, "customFields" => true ]);

        $this->assertEquals(sprintf(
            GetAllChannelMetadata::PATH,
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
        $this->assertEquals("ch", $value->getId());
        $this->assertEquals("ch_name", $value->getName());
        $this->assertEquals("ch_description", $value->getDescription());
        $custom = $value->getCustom();
        $this->assertNotEmpty($custom);
        $this->assertEquals("aa", $custom->a);
        $this->assertEquals("bb", $custom->b);
    }
}

class GetAllChannelMetadataExposed extends GetAllChannelMetadata
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
