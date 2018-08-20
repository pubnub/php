<?php
namespace Tests\Functional;

use PubNub\Endpoints\HistoryDelete;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;


class HistoryDeleteTestTest extends PubNubTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
    }

    public function testHistoryDeleteBasic()
    {
        $historyDelete = new HistoryDeleteExposed($this->pubnub);
        $historyDelete->channel('ch');

        $this->assertEquals(
            sprintf(
                HistoryDelete::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), 'ch'),
            $historyDelete->buildPath());

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid(),
        ], $historyDelete->buildParams());
    }

    public function testHistoryDeleteFull()
    {
        $historyDelete = new HistoryDeleteExposed($this->pubnub);
        $historyDelete
            ->channel('ch')
            ->start(100000)
            ->end(200000);

        $this->assertEquals(
            sprintf(HistoryDelete::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), 'ch'),
            $historyDelete->buildPath()
        );

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid(),
            'start' => '100000',
            'end' => '200000',
        ], $historyDelete->buildParams());
    }
}

class HistoryDeleteExposed extends HistoryDelete
{
    public function buildPath()
    {
        return parent::buildPath();
    }

    public function buildParams()
    {
        return parent::buildParams();
    }
}