<?php

use Pubnub\Pubnub;
use \Pubnub\PubnubException;

class StateTest extends TestCase
{
    protected $channel = 'pubnub_php_test_state';

    /**
     * @group state
     */
    public function testSetAndGetStateForCurrentUser()
    {
        $state = array('name' => 'John Brown', 'status' => 'idle');

        $this->pubnub->setState($this->channel, $state);
        $result = $this->pubnub->getState($this->channel, $this->pubnub->getUUID());

        $this->assertEquals($state['name'], $result['payload']['name']);
        $this->assertEquals($state['status'], $result['payload']['status']);
    }

    /**
     * @group state
     */
    public function testSetAndGetStateForOtherUser()
    {
        $uuid = 'some_other_uuid';
        $state = array('name' => 'Nick', 'status' => 'busy');

        $this->pubnub->setState($this->channel, $state, $uuid);
        $result = $this->pubnub->getState($this->channel, $uuid);

        $this->assertEquals($state['name'], $result['payload']['name']);
        $this->assertEquals($state['status'], $result['payload']['status']);
    }

    /**
     * @group state
     */
    public function testSetChannelGroupState()
    {
        $uuid = 'some_other_uuid';
        $group = 'ptest-' . rand();
        $channels = array('ptest1', 'ptest2');
        $state = array('name' => 'Nick', 'status' => 'busy');

        $this->pubnub->channelGroupAddChannel($group, $channels);

        $this->pubnub->setChannelGroupState($group, $state, $uuid);

        $result = $this->pubnub->getState($channels[1], $uuid);

        $this->assertEquals($state['name'], $result['payload']['name']);
        $this->assertEquals($state['status'], $result['payload']['status']);
    }
}