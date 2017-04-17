<?php

namespace Tests\Functional\Push;

use PubNub\Endpoints\Push\ListPushProvisions;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use Tests\Helpers\StubTransport;


class Modify extends \PubNubTestCase
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

    public function testListChannelGroupGCM()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->pushType(PNPushType::GCM)
            ->deviceId("coolDevice");

        $this->assertEquals(sprintf(
            ListPushProvisions::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "coolDevice"
        ), $list->buildPath());

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "type" => "gcm"
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