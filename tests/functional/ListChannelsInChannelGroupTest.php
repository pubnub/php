<?php

namespace Tests\Functional;

use PubNub\Endpoints\ChannelGroups\ListChannelsInChannelGroup;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class ListChannelsInChannelGroupTest extends \PubNubTestCase
{
    public function testValidatesGroupNotEmpty()
    {
        try {
            $this->pubnub->listChannelsInChannelGroup()->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException$exception) {
            $this->assertEquals("Channel group missing", $exception->getMessage());
        }
    }

    public function testListChannels()
    {
        $listGroup = new ListChannelsInChannelGroupExposed($this->pubnub);

        $listGroup->channelGroup("blah");

        $this->assertEquals(
            sprintf(
                "/v1/channel-registration/sub-key/%s/channel-group/%s",
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                "blah"
            ),
            $listGroup->buildPath()
        );

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
            ],
            $listGroup->buildParams()
        );
    }
}


class ListChannelsInChannelGroupExposed extends ListChannelsInChannelGroup
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