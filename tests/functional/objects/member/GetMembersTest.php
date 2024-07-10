<?php

namespace Tests\Functional\Objects\Member;

use PubNub\Endpoints\Objects\Member\GetMembers;
use PubNub\Models\Consumer\Objects\Member\PNMembersResult;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class GetMembersTest extends PubNubTestCase
{
    public function testGetMembers()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getMembers = new GetMembersExposed($this->pubnub);

        $getMembers
            ->channel("ch")
            ->includeFields([
                "totalCount" => true,
                "customFields" => true,
                "customUUIDFields" => true,
                "UUIDFields" => true
            ]);

        $this->assertEquals(sprintf(
            GetMembersExposed::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "ch"
        ), $getMembers->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "include" => "custom,uuid.custom,uuid",
            "count" => "true"
        ], $getMembers->buildParams());

        $responseData = [
            "status" => 200,
            "data" => [
              [
                "uuid" =>  [
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

        $decoded_data = $getMembers->createResponse($responseData);

        $this->assertNotEmpty($decoded_data);

        $this->assertEquals(1, $decoded_data->getTotalCount());

        $data = $decoded_data->getData();

        $this->assertEquals(1, count($data));

        $resultItem = $data[0];
        $uuid = $resultItem->getUUID();
        $this->assertEquals("uuid", $uuid->getId());
        $this->assertEquals("uuid_name", $uuid->getName());
        $this->assertEquals("uuid_external_id", $uuid->getExternalId());
        $this->assertEquals("uuid_profile_url", $uuid->getProfileUrl());
        $this->assertEquals("uuid_email", $uuid->getEmail());
        $uuidCustom = $uuid->getCustom();
        $this->assertNotEmpty($uuidCustom);
        $this->assertEquals("aa", $uuidCustom->a);
        $this->assertEquals("bb", $uuidCustom->b);

        $custom = $resultItem->getCustom();
        $this->assertNotEmpty($custom);
        $this->assertEquals("aa2", $custom->a);
        $this->assertEquals("bb2", $custom->b);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class GetMembersExposed extends GetMembers
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

    public function createResponse($result): PNMembersResult
    {
        return parent::createResponse($result);
    }
}
