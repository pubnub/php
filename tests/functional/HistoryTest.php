<?php
namespace Tests\Functional;

use PubNub\Endpoints\History;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;


class HistoryTest extends PubNubTestCase
{
    /** @var HistoryExposed */
    private $history;

    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
        $this->history = new HistoryExposed($this->pubnub);
    }

    public function testHistoryBasic()
    {
        $this->history->channel('ch');

        $this->assertEquals(
            sprintf(
                History::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), 'ch'),
                $this->history->buildPath());

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid(),
            'count' => '100'
        ], $this->history->buildParams());
    }

    public function testHistoryFull()
    {
        $this->history
            ->channel('ch')
            ->start(100000)
            ->end(200000)
            ->reverse(false)
            ->count(3)
            ->includeTimetoken(true);

        $this->assertEquals(
            sprintf(History::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), 'ch'),
            $this->history->buildPath()
        );

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid(),
            'count' => '3',
            'start' => '100000',
            'end' => '200000',
            'reverse' => 'false',
            'include_token' => 'true'
        ], $this->history->buildParams());
    }
}

class HistoryExposed extends History
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