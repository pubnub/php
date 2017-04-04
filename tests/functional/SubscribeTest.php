<?php

namespace Tests\Functional;

use PubNub\Endpoints\PubSub\Subscribe;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class SubscribeTest extends \PubNubTestCase
{
    /** @var  ExposedSubscribe */
    protected $sub;

    public function setUp()
    {
        parent::setUp();

        $this->sub = new ExposedSubscribe($this->pubnub);
    }

    public function testValidatesNonEmpty()
    {
        $subscribe = new Subscribe($this->pubnub);

        try {
            $subscribe->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException$exception) {
            $this->assertEquals("At least one channel or channel group should be specified", $exception->getMessage());
        }
    }

    public function testSubSingleChannel()
    {
        $this->sub->channels("ch");

        $this->assertEquals(
            sprintf(Subscribe::PATH, $this->config->getSubscribeKey(), "ch"),
            $this->sub->buildPath()
        );

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->sub->buildParams());

        $this->assertEquals(['ch'], $this->sub->getChannels());
    }

    public function testSubMultipleChannelsUsingList()
    {
        $this->sub->channels(["ch1", "ch2", "ch3"]);

        $this->assertEquals(
            sprintf(Subscribe::PATH, $this->config->getSubscribeKey(), "ch1,ch2,ch3"),
            $this->sub->buildPath()
        );

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->sub->buildParams());

        $this->assertEquals(["ch1", "ch2", "ch3"], $this->sub->getChannels());
    }

    public function testSubMultipleChannelsUsingString()
    {
        $this->sub->channels("ch1,ch2,ch3");

        $this->assertEquals(
            sprintf(Subscribe::PATH, $this->config->getSubscribeKey(), "ch1,ch2,ch3"),
            $this->sub->buildPath()
        );

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->sub->buildParams());

        $this->assertEquals(["ch1", "ch2", "ch3"], $this->sub->getChannels());
    }

    public function testSubMultipleGroupsUsingList()
    {
        $this->sub->channelGroups(["cg1", "cg2", "cg3"]);

        $this->assertEquals(
            sprintf(Subscribe::PATH, $this->config->getSubscribeKey(), ","),
            $this->sub->buildPath()
        );

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid(),
            'channel-group' => "cg1,cg2,cg3"
        ], $this->sub->buildParams());

        $this->assertEquals(["cg1", "cg2", "cg3"], $this->sub->getChannelGroups());
    }

    public function testSubMultipleGroupsUsingString()
    {
        $this->sub->channelGroups("cg1,cg2,cg3");

        $this->assertEquals(
            sprintf(Subscribe::PATH, $this->config->getSubscribeKey(), ","),
            $this->sub->buildPath()
        );

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid(),
            'channel-group' => "cg1,cg2,cg3"
        ], $this->sub->buildParams());

        $this->assertEquals(["cg1", "cg2", "cg3"], $this->sub->getChannelGroups());
    }

    public function testSubMixed()
    {
        $this->sub->channels("ch1");
        $this->sub->channelGroups("cg1,cg2,cg3");
        $this->sub->setFilterExpression("blah");
        $this->sub->setRegion("us-east-1");
        $this->sub->setTimetoken(123);

        $this->assertEquals(
            sprintf(Subscribe::PATH, $this->config->getSubscribeKey(), "ch1"),
            $this->sub->buildPath()
        );

        $this->assertEquals([
            "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid(),
            'channel-group' => "cg1,cg2,cg3",
            'tr' => 'us-east-1',
            'tt' => '123',
            'filter-expr' => 'blah'

        ], $this->sub->buildParams());

        $this->assertEquals(["ch1"], $this->sub->getChannels());
        $this->assertEquals(["cg1", "cg2", "cg3"], $this->sub->getChannelGroups());
    }
}


class ExposedSubscribe extends Subscribe
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
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return array
     */
    public function getChannelGroups()
    {
        return $this->channelGroups;
    }
}
