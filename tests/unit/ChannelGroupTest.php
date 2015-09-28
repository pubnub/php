<?php

class ChannelGroupTest extends TestCase
{
    private $channelGroup;
    private $channelNamespace;

    public static function setUpBeforeClass()
    {
        self::cleanup();
    }

    public function setUp()
    {
        parent::setUp();
        $this->channelGroup = "ptest-" . rand();
        $this->channelNamespace = "ptest-namespace";
    }

    /**
     * @group cg
     */
    public function testAddChannelToNonNameSpacedGroup()
    {
        $channels = array('ch1');

        $result = $this->pubnub->channelGroupAddChannel($this->channelGroup, $channels);

        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupListChannels($this->channelGroup);

        $payload = $result['payload'];

        $this->assertEquals($this->channelGroup, $payload['group']);
        $this->assertEquals("ch1", join(',', $payload['channels']));
    }

    /**
     * @group cg
     */
    public function testAddChannelToNameSpacedGroup()
    {
        $channels = array('ch1');

        $result = $this->pubnub->channelGroupAddChannel($this->channelNamespace . ":" . $this->channelGroup, $channels);

        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupListChannels($this->channelNamespace . ":" . $this->channelGroup);

        $payload = $result['payload'];

        $this->assertEquals($this->channelGroup, $payload['group']);
        $this->assertEquals("ch1", join(',', $payload['channels']));
    }

    /**
     * @group cg
     */
    public function testAddChannelsToNonNameSpacedGroup()
    {
        $channels = array('ch1', 'ch2', 'ch3');

        $result = $this->pubnub->channelGroupAddChannel($this->channelGroup, $channels);

        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupListChannels($this->channelGroup);

        $payload = $result['payload'];

        $this->assertEquals($this->channelGroup, $payload['group']);
        $this->assertEquals("ch1,ch2,ch3", join(',', $payload['channels']));
    }

    /**
     * @group cg
     */
    public function testAddChannelsToNameSpacedGroup()
    {
        $channels = array('ch1', 'ch2', 'ch3');

        $result = $this->pubnub->channelGroupAddChannel($this->channelNamespace . ":" . $this->channelGroup, $channels);

        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupListChannels($this->channelNamespace . ":" . $this->channelGroup);

        $payload = $result['payload'];

        $this->assertEquals($this->channelGroup, $payload['group']);
        $this->assertEquals("ch1,ch2,ch3", join(',', $payload['channels']));
    }

    /**
     * @group cg
     */
    public function testGetChannelsOnNonNameSpacedGroup()
    {
        $channels = array('ch1', 'ch2', 'ch3', 'ch4', 'ch5');

        $result = $this->pubnub->channelGroupAddChannel($this->channelGroup, $channels);

        $this->assertEquals('OK', $result['message']);
        $result = $this->pubnub->channelGroupListChannels($this->channelGroup);
        $payload = $result['payload'];

        $this->assertEquals($this->channelGroup, $payload['group']);
        $this->assertEquals(join(',', $channels), join(',', $payload['channels']));
    }

    /**
     * @group cg
     */
    public function testGetChannelsOnNameSpacedGroup()
    {
        $channels = array('ch1', 'ch2', 'ch3', 'ch4', 'ch5');

        $result = $this->pubnub->channelGroupAddChannel($this->channelNamespace . ":" . $this->channelGroup, $channels);

        $this->assertEquals('OK', $result['message']);
        $result = $this->pubnub->channelGroupListChannels($this->channelNamespace . ":" . $this->channelGroup);
        $payload = $result['payload'];

        $this->assertEquals($this->channelGroup, $payload['group']);
        $this->assertEquals(join(',', $channels), join(',', $payload['channels']));
    }

    /**
     * @group cg
     */
    public function testRemoveChannelsFromNonNameSpacedGroup()
    {
        $channels = array('ch1', 'ch2', 'ch3', 'ch4', 'ch5');

        $result = $this->pubnub->channelGroupAddChannel($this->channelGroup, $channels);
        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupListChannels($this->channelGroup);

        $payload = $result['payload'];
        $this->assertEquals($this->channelGroup, $payload['group']);
        $this->assertEquals("ch1,ch2,ch3,ch4,ch5", join(',', $payload['channels']));

        $result = $this->pubnub->channelGroupRemoveChannel($this->channelGroup, array('ch2', 'ch4'));
        $this->assertEquals('OK', $result['message']);

        sleep(1);
        $result = $this->pubnub->channelGroupListChannels($this->channelGroup);

        $payload = $result['payload'];
        $this->assertEquals($this->channelGroup, $payload['group']);
        $this->assertEquals("ch1,ch3,ch5", join(',', $payload['channels']));
    }

    /**
     * @group cg
     */
    public function testRemoveChannelsFromNameSpacedGroup()
    {
        $channels = array('ch1', 'ch2', 'ch3', 'ch4', 'ch5');

        $result = $this->pubnub->channelGroupAddChannel($this->channelNamespace . ":" . $this->channelGroup, $channels);
        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupListChannels($this->channelNamespace . ":" . $this->channelGroup);

        $payload = $result['payload'];
        $this->assertEquals($this->channelGroup, $payload['group']);
        $this->assertEquals("ch1,ch2,ch3,ch4,ch5", join(',', $payload['channels']));

        $result = $this->pubnub->channelGroupRemoveChannel($this->channelNamespace . ":" . $this->channelGroup, array('ch2', 'ch4'));
        $this->assertEquals('OK', $result['message']);

        sleep(1);
        $result = $this->pubnub->channelGroupListChannels($this->channelNamespace . ":" . $this->channelGroup);

        $payload = $result['payload'];
        $this->assertEquals($this->channelGroup, $payload['group']);
        $this->assertEquals("ch1,ch3,ch5", join(',', $payload['channels']));
    }

    /**
     * @group cg
     */
    public function testRemoveGroup()
    {
        $ch1 = "ch1" . rand();

        $result = $this->pubnub->channelGroupAddChannel($this->channelGroup, array($ch1));
        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupRemoveGroup($this->channelGroup);
        $this->assertEquals('OK', $result['message']);
    }
}
