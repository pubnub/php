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

    public function setUp(): void
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
        $this->hereNow = new ExposedHereNow($this->pubnub);
    }

    public function testHereNow()
    {
        $this->hereNow->channels("ch1");

        $this->assertEquals(
            sprintf(ExposedHereNow::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), "ch1"),
            $this->hereNow->buildPath()
        );

        $this->assertEquals([
            'limit' => '1000',
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->hereNow->buildParams());
    }

    public function testHereNowGroups()
    {
        $this->hereNow->channelGroups("gr1");

        $this->assertEquals(
            sprintf(ExposedHereNow::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), ","),
            $this->hereNow->buildPath()
        );

        $this->assertEquals([
            'channel-group' => 'gr1',
            'limit' => '1000',
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
            sprintf(ExposedHereNow::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), "ch1"),
            $this->hereNow->buildPath()
        );

        $this->assertEquals([
            'channel-group' => 'gr1',
            'state' => '1',
            'disable-uuids' => '1',
            'limit' => '1000',
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->hereNow->buildParams());
    }

    public function testHereNowWithLimit(): void
    {
        $this->hereNow
            ->channels("ch1")
            ->limit(50);

        $this->assertEquals(
            sprintf(ExposedHereNow::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), "ch1"),
            $this->hereNow->buildPath()
        );

        $this->assertEquals([
            'limit' => '50',
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->hereNow->buildParams());
    }

    public function testHereNowWithLimitAndOffset(): void
    {
        $this->hereNow
            ->channels("ch1")
            ->limit(50)
            ->offset(10);

        $this->assertEquals(
            sprintf(ExposedHereNow::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), "ch1"),
            $this->hereNow->buildPath()
        );

        $this->assertEquals([
            'limit' => '50',
            'offset' => '10',
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->hereNow->buildParams());
    }

    public function testHereNowWithLimitZero(): void
    {
        $this->hereNow
            ->channels("ch1")
            ->limit(0);

        $this->assertEquals(
            sprintf(ExposedHereNow::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), "ch1"),
            $this->hereNow->buildPath()
        );

        $this->assertEquals([
            'limit' => '0',
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->hereNow->buildParams());
    }
}


// phpcs:ignore PSR1.Classes.ClassDeclaration
class ExposedHereNow extends HereNow
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
}
