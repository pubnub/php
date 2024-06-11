<?php

namespace Tests\Functional\Push;

use PubNub\Endpoints\Push\ListPushProvisions;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;
use Tests\Helpers\StubTransport;

class ListPushProvisionsTest extends PubNubTestCase
{
    public function testListChannelGroupAPNS()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->pushType(PNPushType::APNS)
            ->deviceId("coolDevice");

        $this->assertEquals(sprintf(
            ListPushProvisions::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $list->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "apns"
        ], $list->buildParams());
    }

    public function testListChannelGroupAPNS2()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->pushType(PNPushType::APNS2)
            ->deviceId("coolDevice")
            ->topic("coolTopic")
            ->environment("production");

        $this->assertEquals(sprintf(
            ListPushProvisions::PATH_APNS2,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $list->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "topic" => "coolTopic",
            "environment" => "production"
        ], $list->buildParams());
    }

    public function testListChannelGroupFCM()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->pushType(PNPushType::FCM)
            ->deviceId("coolDevice");

        $this->assertEquals(sprintf(
            ListPushProvisions::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $list->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "fcm"
        ], $list->buildParams());
    }

    public function testListChannelGroupMPNS()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->pushType(PNPushType::MPNS)
            ->deviceId("coolDevice");

        $this->assertEquals(sprintf(
            ListPushProvisions::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $list->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "mpns"
        ], $list->buildParams());
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class ListPushProvisionsExposed extends ListPushProvisions
{
    protected $transport;

    public function __construct(PubNub $pubnubInstance)
    {
        parent::__construct($pubnubInstance);

        $this->transport = new StubTransport();
    }

    public function stubFor($url)
    {
        return $this->transport->stubFor($url);
    }

    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }

    public function requestOptions()
    {
        return [
            'transport' => $this->transport
        ];
    }
}
