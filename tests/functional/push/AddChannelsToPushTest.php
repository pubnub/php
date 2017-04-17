<?php

namespace Tests\Functional\Push;

use PubNub\Endpoints\Push\AddChannelsToPush;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;


class AddChannelsToPushTest extends PubNubTestCase
{
    public function testPushAddSingleChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $add = new AddChannelsToPushExposed($this->pubnub);

        $add->channels("ch")
            ->pushType(PNPushType::APNS)
            ->deviceId("coolDevice");

        $this->assertEquals(sprintf(
            AddChannelsToPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $add->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "apns",
            "add" => "ch"
        ], $add->buildParams());

        $this->assertEquals(["ch"], $add->getChannels());
    }

    public function testPushAddMultipleChannels()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $add = new AddChannelsToPushExposed($this->pubnub);

        $add->channels(["ch1", "ch2"])
            ->pushType(PNPushType::MPNS)
            ->deviceId("coolDevice");

        $this->assertEquals(sprintf(
            AddChannelsToPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $add->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "mpns",
            "add" => "ch1,ch2"
        ], $add->buildParams());

        $this->assertEquals(["ch1","ch2"], $add->getChannels());
    }

    public function testPushAddGoogle()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $add = new AddChannelsToPushExposed($this->pubnub);

        $add->channels(["ch1", "ch2", "ch3"])
            ->pushType(PNPushType::GCM)
            ->deviceId("coolDevice");

        $this->assertEquals(sprintf(
            AddChannelsToPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $add->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "gcm",
            "add" => "ch1,ch2,ch3"
        ], $add->buildParams());

        $this->assertEquals(["ch1","ch2","ch3"], $add->getChannels());
    }
}


class AddChannelsToPushExposed extends AddChannelsToPush
{
    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }

    public function getChannels()
    {
        return $this->channels;
    }
}
