<?php
namespace Tests\Functional;

use PubNub\PubNub;
use PubNub\Endpoints\Presence\HereNow;
use PubNub\PubNubUtil;
use PubNubTestCase;


class HereNowTest extends PubNubTestCase
{
    /** @var  ExposedHereNow */
    protected $hereNow;

    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
        $this->hereNow = new ExposedHereNow($this->pubnub);
    }

    public function testHereNow()
    {
        $this->hereNow->channels("ch1");

        $this->assertEquals(sprintf(HereNow::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            "ch1"), $this->hereNow->buildPath());

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->hereNow->buildParams());
    }

    public function testHereNowGroups()
    {
        $this->hereNow->channelGroups("gr1");

        $this->assertEquals(
            sprintf(HereNow::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), ","),
            $this->hereNow->buildPath()
        );

        $this->assertEquals([
            'channel-group' => 'gr1',
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->hereNow->buildParams());
    }

    public function testHereNowWithOptions()
    {
        $this->hereNow
            ->channels("ch1")
            ->channelGroups("gr1")
            ->includeState(true)
            ->includeUuids(false);

        $this->assertEquals(
            sprintf(HereNow::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), "ch1"),
            $this->hereNow->buildPath()
        );

        $this->assertEquals([
            'channel-group' => 'gr1',
            'state' => '1',
            'disable-uuids' => '1',
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->hereNow->buildParams());
    }
}

class ExposedHereNow extends HereNow
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
