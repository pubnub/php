<?php

namespace Tests\Functional\Objects\Membership;

use PubNub\Endpoints\Objects\Membership\GetMemberships;
use PubNub\Models\Consumer\Objects\Membership\PNMembershipsResult;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class GetMembershipsTest extends PubNubTestCase
{
    public function testGetMemberships()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getMemberships = new GetMembershipsExposed($this->pubnub);

        $getMemberships
            ->uuid("uuid")
            ->includeFields([
                "totalCount" => true,
                "customFields" => true,
                "customChannelFields" => true,
                "channelFields" => true
            ]);

        $this->assertEquals(sprintf(
            GetMembershipsExposed::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "uuid"
        ), $getMemberships->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom,channel.custom,channel",
            "count" => "true"
        ], $getMemberships->buildParams());

        $responseData = [
            "status" => 200,
            "data" => [
              [
                "channel" =>  [
                        "id" => "ch",
                        "name" => "ch_name",
                        "description" => "ch_description",
                        "custom" => [
                            "a" => "aa",
                            "b" => "bb"
                        ],
                        "updated" => "2019-02-20T23:11:20.893755",
                        "eTag" => "RTc1NUQwNUItREMyNy00Q0YxLUJCNDItMEZDMTZDMzVCN0VGCg=="
                    ],
                    "custom" => [
                        "a" => "aa2",
                        "b" => "bb2",
                    ],
                    "updated" => "2019-02-20T23:11:20.893755",
                    "eTag" => "RTc1NUQwNUItREMyNy00Q0YxLUJCNDItMEZDMTZDMzVCN0VGCg=="
              ]
            ],
            "totalCount" => 1,
            "next" => "MUIwQTAwMUItQkRBRC00NDkyLTgyMEMtODg2OUU1N0REMTNBCg==",
            "prev" => "M0FFODRENzMtNjY2Qy00RUExLTk4QzktNkY1Q0I2MUJFNDRCCg=="
        ];

        $decoded_data = $getMemberships->createResponse($responseData);

        $this->assertNotEmpty($decoded_data);

        $this->assertEquals(1, $decoded_data->getTotalCount());

        $data = $decoded_data->getData();

        $this->assertEquals(1, count($data));

        $resultItem = $data[0];
        $channel = $resultItem->getChannel();
        $this->assertEquals("ch", $channel->getId());
        $this->assertEquals("ch_name", $channel->getName());
        $this->assertEquals("ch_description", $channel->getDescription());
        $channelCustom = $channel->getCustom();
        $this->assertNotEmpty($channelCustom);
        $this->assertEquals("aa", $channelCustom->a);
        $this->assertEquals("bb", $channelCustom->b);

        $custom = $resultItem->getCustom();
        $this->assertNotEmpty($custom);
        $this->assertEquals("aa2", $custom->a);
        $this->assertEquals("bb2", $custom->b);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class GetMembershipsExposed extends GetMemberships
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

    public function createResponse($result): PNMembershipsResult
    {
        return parent::createResponse($result);
    }
}
