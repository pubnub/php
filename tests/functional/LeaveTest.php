<?php

namespace Tests\Integrational;

use PubNub\PubNub;
use PubNub\Endpoints\Presence\Leave;
use PubNub\PubNubUtil;


class LeaveTest extends \PubNubTestCase
{
    /** @var  LeaveExposed */
    protected $leave;

    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
        $this->leave = new LeaveExposed($this->pubnub);
    }

    public function testLeaveSingleChannel()
    {
        $this->leave->channels("ch");

        $this->assertEquals(
            sprintf(LeaveExposed::PATH, $this->config->getSubscribeKey(), "ch"),
            $this->leave->buildPath()
        );

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid()
        ], $this->leave->buildParams());

        $this->assertEquals(["ch"], $this->leave->getChannels());
    }

    public function testLeaveMultipleChannels()
    {
        $this->leave->channels("ch1,ch2,ch3");

        $this->assertEquals(
            sprintf(Leave::PATH, $this->config->getSubscribeKey(), "ch1,ch2,ch3"),
            $this->leave->buildPath()
        );

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid()
        ], $this->leave->buildParams());

        $this->assertEquals(["ch1", "ch2", "ch3"], $this->leave->getChannels());
    }

    public function testLeaveMultipleChannelsUsingArray()
    {
        $this->leave->channels(["ch1","ch2","ch3"]);

        $this->assertEquals(
            sprintf(Leave::PATH, $this->config->getSubscribeKey(), "ch1,ch2,ch3"),
            $this->leave->buildPath()
        );

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid()
        ], $this->leave->buildParams());

        $this->assertEquals(["ch1", "ch2", "ch3"], $this->leave->getChannels());
    }

    public function testLeaveSingleGroup()
    {
        $this->leave->channelGroups("gr");

        $this->assertEquals(
            sprintf(Leave::PATH, $this->config->getSubscribeKey(), ","),
            $this->leave->buildPath()
        );

        $this->assertEquals([
            "channel-group" => "gr",
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid()
        ], $this->leave->buildParams());

        $this->assertEquals(["gr"], $this->leave->getGroups());
    }

    public function testLeaveMultipleGroups()
    {
        $this->leave->channelGroups("gr1,gr2,gr3");

        $this->assertEquals(
            sprintf(Leave::PATH, $this->config->getSubscribeKey(), ","),
            $this->leave->buildPath()
        );

        $this->assertEquals([
            "channel-group" => "gr1,gr2,gr3",
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid()
        ], $this->leave->buildParams());

        $this->assertEquals(["gr1","gr2","gr3"], $this->leave->getGroups());
    }

    public function testLeaveMultipleGroupsUsingArray()
    {
        $this->leave->channelGroups(["gr1","gr2","gr3"]);

        $this->assertEquals(
            sprintf(Leave::PATH, $this->config->getSubscribeKey(), ","),
            $this->leave->buildPath()
        );

        $this->assertEquals([
            "channel-group" => "gr1,gr2,gr3",
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid()
        ], $this->leave->buildParams());

        $this->assertEquals(["gr1","gr2","gr3"], $this->leave->getGroups());
    }

    public function testLeaveChannelsAndGroups()
    {
        $this->leave->channels("ch1,ch2")->channelGroups(["gr1", "gr2"]);

        $this->assertEquals(
            sprintf(Leave::PATH, $this->config->getSubscribeKey(), "ch1,ch2"),
            $this->leave->buildPath()
        );

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            "channel-group" => "gr1,gr2"
        ], $this->leave->buildParams());

        $this->assertEquals(["gr1", "gr2"], $this->leave->getGroups());
        $this->assertEquals(["ch1", "ch2"], $this->leave->getChannels());
    }
}


class LeaveExposed extends Leave
{
    public function buildPath()
    {
        return parent::buildPath();
    }

    public function buildParams()
    {
        return parent::buildParams();
    }

    /**
     * @return \string[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return \string[]
     */
    public function getGroups()
    {
        return $this->groups;
    }
}