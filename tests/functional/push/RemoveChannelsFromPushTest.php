<?php

namespace Tests\Functional\Push;

use PubNub\Endpoints\Push\RemoveChannelsFromPush;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;

class RemoveChannelsFromPushTest extends PubNubTestCase
{
    public function testPushRemoveSingleChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushExposed($this->pubnub);

        $remove->channels('ch')
            ->pushType(PNPushType::APNS)
            ->deviceId('coolDevice');

        $this->assertEquals(sprintf(
            RemoveChannelsFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $remove->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "apns",
            "remove" => "ch"
        ], $remove->buildParams());
    }

    public function testPushRemoveMultipleChannels()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushExposed($this->pubnub);

        $remove->channels(['ch1', 'ch2'])
            ->pushType(PNPushType::FCM)
            ->deviceId('coolDevice');

        $this->assertEquals(sprintf(
            RemoveChannelsFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $remove->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "fcm",
            "remove" => "ch1,ch2"
        ], $remove->buildParams());
    }

    public function testPushRemoveFCM()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushExposed($this->pubnub);

        $remove->channels(['ch1', 'ch2', 'ch3'])
            ->pushType(PNPushType::FCM)
            ->deviceId('coolDevice');

        $this->assertEquals(sprintf(
            RemoveChannelsFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $remove->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "fcm",
            "remove" => "ch1,ch2,ch3"
        ], $remove->buildParams());
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class RemoveChannelsFromPushExposed extends RemoveChannelsFromPush
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
