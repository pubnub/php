<?php

namespace Tests\Functional;

use PubNub\Endpoints\ChannelGroups\AddChannelToChannelGroup;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class AddChannelToChannelGroupTest extends \PubNubTestCase
{
    public function testValidatesGroupNotEmpty()
    {
        try {
            $this->pubnub->addChannelToChannelGroup()->channels("blah")->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException$exception) {
            $this->assertEquals("Channel group missing", $exception->getMessage());
        }
    }

    public function testValidatesChannelsNotEmpty()
    {
        try {
            $this->pubnub->addChannelToChannelGroup()->channelGroup("blah")->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException$exception) {
            $this->assertEquals("Channels missing", $exception->getMessage());
        }
    }

    public function testAddSingleChannel()
    {
        $add = new AddChannelToChannelGroupExposed($this->pubnub);

        $add->channels("ch")->channelGroup("blah");

        $this->assertEquals(
            sprintf(
                "/v1/channel-registration/sub-key/%s/channel-group/%s",
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                "blah"
            ),
            $add->buildPath()
        );

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "add" => 'ch'
            ],
            $add->buildParams()
        );
    }


    public function testAddMultipleChannels()
    {
        $add = new AddChannelToChannelGroupExposed($this->pubnub);

        $add->channels(["ch1", "ch2", "ch3"])->channelGroup("blah");

        $this->assertEquals(
            sprintf(
                "/v1/channel-registration/sub-key/%s/channel-group/%s",
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                "blah"
            ),
            $add->buildPath()
        );

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "add" => 'ch1,ch2,ch3'
            ],
            $add->buildParams()
        );
    }
}


class AddChannelToChannelGroupExposed extends AddChannelToChannelGroup
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