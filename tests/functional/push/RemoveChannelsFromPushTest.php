<?php

namespace Tests\Functional\Push;

use PubNub\Endpoints\Push\RemoveChannelsFromPush;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class RemoveChannelsFromPushTest extends \PubNubTestCase
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
            ->pushType(PNPushType::MPNS)
            ->deviceId('coolDevice');

        $this->assertEquals(sprintf(
            RemoveChannelsFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $remove->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "mpns",
            "remove" => "ch1,ch2"
        ], $remove->buildParams());
    }

    public function testPushRemoveGoogle()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushExposed($this->pubnub);

        $remove->channels(['ch1', 'ch2', 'ch3'])
            ->pushType(PNPushType::GCM)
            ->deviceId('coolDevice');

        $this->assertEquals(sprintf(
            RemoveChannelsFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $remove->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "gcm",
            "remove" => "ch1,ch2,ch3"
        ], $remove->buildParams());
    }
}


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