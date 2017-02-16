<?php

use \PubNub\Models\Consumer\ChannelGroup\PNChannelGroupsAddChannelResult;
use \PubNub\Models\Consumer\ChannelGroup\PNChannelGroupsListChannelsResult;
use PubNub\Models\Consumer\ChannelGroup\PNChannelGroupsRemoveChannelResult;
use \PubNub\Models\Consumer\ChannelGroup\PNChannelGroupsRemoveGroupResult;

class ChannelGroupsTest extends PubNubTestCase
{
    /**
     * @group cg
     * @group cg-integrational
     */
    public function testSingleChannel()
    {
        $ch = "channel-groups-native-ch";
        $gr = "channel-groups-native-cg";

        //cleanup
        $this->pubnub->removeChannelGroup()->group($gr)->sync();

        // add
        $response = $this->pubnub->addChannelToChannelGroup()->channels($ch)->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsAddChannelResult);

        sleep(1);

        // list
        $response = $this->pubnub->listChannelsInChannelGroup()->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsListChannelsResult);

        $this->assertEquals(1, count($response->getChannels()));
        $this->assertEquals($ch, $response->getChannels()[0]);

        // remove
        $response = $this->pubnub->removeChannelFromChannelGroup()->channels($ch)->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsRemoveChannelResult);

        sleep(1);

        // list
        $response = $this->pubnub->listChannelsInChannelGroup()->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsListChannelsResult);

        $this->assertEquals(0, count($response->getChannels()));
    }

    // TODO testMultipleChannels
    public function testMultipleChannels()
    {
        $ch1 = "channel-groups-native-ch1";
        $ch2 = "channel-groups-native-ch2";
        $gr = "channel-groups-native-cg";

        // cleanup
        $this->pubnub->removeChannelGroup()->group($gr)->sync();

        // add
        $response = $this->pubnub->addChannelToChannelGroup()->channels([$ch1, $ch2])->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsAddChannelResult);

        sleep(1);

        // list
        $response = $this->pubnub->listChannelsInChannelGroup()->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsListChannelsResult);
        $this->assertEquals(2, count($response->getChannels()));
        $this->assertEquals(true, in_array($ch1, $response->getChannels()));
        $this->assertEquals(true, in_array($ch2, $response->getChannels()));

        // remove
        $response = $this->pubnub->removeChannelFromChannelGroup()->channels([$ch1, $ch2])->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsRemoveChannelResult);

        sleep(1);

        // list
        $response = $this->pubnub->listChannelsInChannelGroup()->group($gr)->sync();
        $this->assertEquals(true, $response instanceof PNChannelGroupsListChannelsResult);
        $this->assertEquals(0, count($response->getChannels()));
    }

    public function testAddChannelRemoveGroup()
    {
        $ch = "channel-groups-native-ch";
        $gr = "channel-groups-native-cg";

        // cleanup
        $this->pubnub->removeChannelGroup()->group($gr)->sync();

        // add
        $response = $this->pubnub->addChannelToChannelGroup()->channels($ch)->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsAddChannelResult);

        sleep(1);

        // list
        $response = $this->pubnub->listChannelsInChannelGroup()->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsListChannelsResult);

        $this->assertEquals(1, count($response->getChannels()));
        $this->assertEquals($ch, $response->getChannels()[0]);

        // remove
        $response = $this->pubnub->removeChannelGroup()->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsRemoveGroupResult);

        sleep(1);

        // list
        $response = $this->pubnub->listChannelsInChannelGroup()->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsListChannelsResult);

        $this->assertEquals(0, count($response->getChannels()));
    }

    public function testSuperCall()
    {
        $ch = "channel-groups-native-ch";
        $gr = "channel-groups-native-cg";

        // TODO: replace with super-instance
        // add
        $response = $this->pubnub->addChannelToChannelGroup()->channels($ch)->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsAddChannelResult);

        // list
        $response = $this->pubnub->listChannelsInChannelGroup()->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsListChannelsResult);

        // remove channel
        $response = $this->pubnub->removeChannelFromChannelGroup()->group($gr)->channels($ch)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsRemoveChannelResult);

        // remove group
        $response = $this->pubnub->removeChannelGroup()->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsRemoveGroupResult);

        // list
        $response = $this->pubnub->listChannelsInChannelGroup()->group($gr)->sync();

        $this->assertEquals(true, $response instanceof PNChannelGroupsListChannelsResult);
    }
}
