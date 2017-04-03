<?php

namespace Tests\Functional;

use PubNub\Endpoints\ChannelGroups\RemoveChannelFromChannelGroup;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class RemoveChannelFromChannelGroupTest extends \PubNubTestCase
{
    public function testValidatesGroupNotEmpty()
    {
        try {
            $this->pubnub->removeChannelFromChannelGroup()->channels("blah")->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException$exception) {
            $this->assertEquals("Channel group missing", $exception->getMessage());
        }
    }

    public function testValidatesChannelGroupsNotEmpty()
    {
        try {
            $this->pubnub->removeChannelFromChannelGroup()->channelGroup("blah")->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException$exception) {
            $this->assertEquals("Channels missing", $exception->getMessage());
        }
    }

    public function testRemoveSingleChannel()
    {
        $remove = new RemoveChannelChannelGroupExposed($this->pubnub);

        $remove->channels("ch")->channelGroup("blah");

        $this->assertEquals(
            sprintf(
                "/v1/channel-registration/sub-key/%s/channel-group/%s",
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                "blah"
            ),
            $remove->buildPath()
        );

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "remove" => 'ch'
            ],
            $remove->buildParams()
        );
    }


    public function testRemoveMultipleChannels()
    {
        $remove = new RemoveChannelChannelGroupExposed($this->pubnub);

        $remove->channels(["ch1", "ch2", "ch3"])->channelGroup("blah");

        $this->assertEquals(
            sprintf(
                "/v1/channel-registration/sub-key/%s/channel-group/%s",
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                "blah"
            ),
            $remove->buildPath()
        );

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "remove" => 'ch1,ch2,ch3'
            ],
            $remove->buildParams()
        );
    }
}


class RemoveChannelChannelGroupExposed extends RemoveChannelFromChannelGroup
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