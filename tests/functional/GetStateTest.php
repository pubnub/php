<?php
namespace Tests\Functional;

use PubNub\Endpoints\Presence\GetState;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;


class GetStateTest extends PubNubTestCase
{
    /** @var  ExposedGetState */
    protected $getState;

    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
        $this->getState = new ExposedGetState($this->pubnub);
    }

    public function testGetStateSingleChannel()
    {
        $this->getState->channels("ch");

        $this->assertEquals(
            sprintf(
                GetState::PATH,
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                "ch",
                $this->pubnub->getConfiguration()->getUuid()
            ), $this->getState->buildPath());

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->getState->buildParams());

        $this->assertEquals(["ch"], $this->getState->getChannels());
    }

    public function testGetStateSingleGroup()
    {
        $this->getState->channelGroups("gr");

        $this->assertEquals(
            sprintf(
                GetState::PATH,
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                ",",
                $this->pubnub->getConfiguration()->getUuid()
            ), $this->getState->buildPath());

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid(),
            'channel-group' => "gr"
        ], $this->getState->buildParams());

        $this->assertEquals(0, count($this->getState->getChannels()));
        $this->assertEquals(["gr"], $this->getState->getChannelGroups());
    }
}


class ExposedGetState extends GetState
{
    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }

    /**
     * @return string[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return string[]
     */
    public function getChannelGroups()
    {
        return $this->channelGroups;
    }
}