<?php

namespace Tests\Functional\Push;

use PubNub\Endpoints\Push\RemoveDeviceFromPush;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class RemoveDeviceFromPushTest extends PubNubTestCase
{
    public function testRemovePushAPNS()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushExposed($this->pubnub);

        $remove->pushType(PNPushType::APNS)
            ->deviceId('coolDevice');

        $this->assertEquals(sprintf(
            RemoveDeviceFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $remove->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "apns",
        ], $remove->buildParams());
    }

    public function testRemovePushFCM()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushExposed($this->pubnub);

        $remove->pushType(PNPushType::FCM)
            ->deviceId('coolDevice');

        $this->assertEquals(sprintf(
            RemoveDeviceFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $remove->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "fcm",
        ], $remove->buildParams());
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class RemoveDeviceFromPushExposed extends RemoveDeviceFromPush
{
    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }
}
