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
    public function testGetAllChannelGroupNames()
    {
        $group1 = "ptest_group1" . rand();
        $group2 = "ptest_group2" . rand();
        $ch1 = "ch1" . rand();
        $ch2 = "ch2" . rand();

        $result = $this->pubnub->channelGroupAddChannel($group1, array($ch1));
        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupAddChannel($group2, array($ch2));
        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupListGroups();

        $this->assertTrue(in_array($group1, $result["payload"]["groups"]));
        $this->assertTrue(in_array($group2, $result["payload"]["groups"]));
        $this->assertEquals("", $result["payload"]["namespace"]);
    }

    /**
     * @group cg
     */
    public function testGetAllChannelGroupNamesNamespace()
    {
        $group1 = "ptest_group1" . rand();
        $group2 = "ptest_group2" . rand();
        $ch1 = "ch1" . rand();
        $ch2 = "ch2" . rand();

        $result = $this->pubnub->channelGroupAddChannel($this->channelNamespace . ":" . $group1, array($ch1));
        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupAddChannel($this->channelNamespace . ":" . $group2, array($ch2));
        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupListGroups($this->channelNamespace);
        $payload = $result["payload"];

        $this->assertTrue(in_array($group1, $payload["groups"]));
        $this->assertTrue(in_array($group2, $payload["groups"]));
        $this->assertEquals($this->channelNamespace, $payload["namespace"]);
    }

    /**
     * @group cg
     */
    public function testRemoveGroup()
    {
        $ch1 = "ch1" . rand();

        $result = $this->pubnub->channelGroupAddChannel($this->channelGroup, array($ch1));
        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupListGroups();
        $this->assertEquals('', $result['payload']['namespace']);
        $this->assertTrue(in_array($this->channelGroup, $result["payload"]["groups"]));

        $result = $this->pubnub->channelGroupRemoveGroup($this->channelGroup);
        $this->assertEquals('OK', $result['message']);

        sleep(1);
        $result = $this->pubnub->channelGroupListGroups();
        $this->assertEquals('', $result['payload']['namespace']);
        $this->assertFalse(in_array($this->channelGroup, $result["payload"]["groups"]));
    }

    /**
     * @group cg
     */
    public function testRemoveNamespacedGroup()
    {
        $ch1 = "ch1" . rand();

        $result = $this->pubnub->channelGroupAddChannel($this->channelNamespace . ":" . $this->channelGroup, array($ch1));
        $this->assertEquals('OK', $result['message']);

        $result = $this->pubnub->channelGroupListGroups($this->channelNamespace);
        $this->assertEquals($this->channelNamespace, $result["payload"]["namespace"]);
        $this->assertTrue(in_array($this->channelGroup, $result["payload"]["groups"]));

        $result = $this->pubnub->channelGroupRemoveGroup($this->channelNamespace . ":" . $this->channelGroup);
        $this->assertEquals('OK', $result['message']);

        sleep(1);
        $result = $this->pubnub->channelGroupListGroups($this->channelNamespace);
        $this->assertEquals($this->channelNamespace, $result["payload"]["namespace"]);
        $this->assertFalse(in_array($this->channelGroup, $result["payload"]["groups"]));
    }
}
