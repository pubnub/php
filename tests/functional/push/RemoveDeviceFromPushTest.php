<?php

namespace Tests\Functional\Push;

use PubNub\Endpoints\Push\RemoveDeviceFromPush;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class RemoveDeviceFromPushTest extends \PubNubTestCase
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

    public function testRemovePushMPNS()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushExposed($this->pubnub);

        $remove->pushType(PNPushType::MPNS)
            ->deviceId('coolDevice');

        $this->assertEquals(sprintf(
            RemoveDeviceFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $remove->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "mpns",
        ], $remove->buildParams());
    }

    public function testRemovePushGCM()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushExposed($this->pubnub);

        $remove->pushType(PNPushType::GCM)
            ->deviceId('coolDevice');

        $this->assertEquals(sprintf(
            RemoveDeviceFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $remove->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "gcm",
        ], $remove->buildParams());
    }
}


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