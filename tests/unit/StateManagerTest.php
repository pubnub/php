<?php

use PHPUnit\Framework\TestCase;
use PubNub\Managers\StateManager;
use PubNub\Builders\DTO\SubscribeOperation;
use PubNub\Builders\DTO\UnsubscribeOperation;
use PubNub\PNConfiguration;
use PubNub\PubNub;

class StateManagerTest extends TestCase
{
    private PubNub $pubnub;
    private StateManager $stateManager;

    public function setUp(): void
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setPublishKey('demo');
        $config->setUserId('test-user');
        
        $this->pubnub = new PubNub($config);
        $this->stateManager = new StateManager($this->pubnub);
    }

    public function testIsEmptyByDefault()
    {
        $this->assertTrue($this->stateManager->isEmpty());
    }

    public function testAdaptSubscribeBuilderWithChannels()
    {
        $operation = new SubscribeOperation(
            ['channel1', 'channel2'],
            [],
            false,
            null
        );
        
        $this->stateManager->adaptSubscribeBuilder($operation);
        
        $this->assertFalse($this->stateManager->isEmpty());
    }

    public function testAdaptSubscribeBuilderWithChannelGroups()
    {
        $operation = new SubscribeOperation(
            [],
            ['group1', 'group2'],
            false,
            null
        );
        
        $this->stateManager->adaptSubscribeBuilder($operation);
        
        $this->assertFalse($this->stateManager->isEmpty());
    }

    public function testAdaptSubscribeBuilderWithChannelsAndGroups()
    {
        $operation = new SubscribeOperation(
            ['channel1'],
            ['group1'],
            false,
            null
        );
        
        $this->stateManager->adaptSubscribeBuilder($operation);
        
        $this->assertFalse($this->stateManager->isEmpty());
    }

    public function testPrepareChannelListWithoutPresence()
    {
        $operation = new SubscribeOperation(
            ['channel1', 'channel2'],
            [],
            false,
            null
        );
        
        $this->stateManager->adaptSubscribeBuilder($operation);
        $channelList = $this->stateManager->prepareChannelList(false);
        
        $this->assertCount(2, $channelList);
        $this->assertContains('channel1', $channelList);
        $this->assertContains('channel2', $channelList);
    }

    public function testPrepareChannelListWithPresence()
    {
        $operation = new SubscribeOperation(
            ['channel1', 'channel2'],
            [],
            true,
            null
        );
        
        $this->stateManager->adaptSubscribeBuilder($operation);
        $channelList = $this->stateManager->prepareChannelList(true);
        
        $this->assertCount(4, $channelList);
        $this->assertContains('channel1', $channelList);
        $this->assertContains('channel2', $channelList);
        $this->assertContains('channel1-pnpres', $channelList);
        $this->assertContains('channel2-pnpres', $channelList);
    }

    public function testPrepareChannelGroupListWithoutPresence()
    {
        $operation = new SubscribeOperation(
            [],
            ['group1', 'group2'],
            false,
            null
        );
        
        $this->stateManager->adaptSubscribeBuilder($operation);
        $groupList = $this->stateManager->prepareChannelGroupList(false);
        
        $this->assertCount(2, $groupList);
        $this->assertContains('group1', $groupList);
        $this->assertContains('group2', $groupList);
    }

    public function testPrepareChannelGroupListWithPresence()
    {
        $operation = new SubscribeOperation(
            [],
            ['group1', 'group2'],
            true,
            null
        );
        
        $this->stateManager->adaptSubscribeBuilder($operation);
        $groupList = $this->stateManager->prepareChannelGroupList(true);
        
        $this->assertCount(4, $groupList);
        $this->assertContains('group1', $groupList);
        $this->assertContains('group2', $groupList);
        $this->assertContains('group1-pnpres', $groupList);
        $this->assertContains('group2-pnpres', $groupList);
    }

    public function testAdaptUnsubscribeBuilderRemovesChannels()
    {
        // First subscribe
        $subscribeOp = new SubscribeOperation(
            ['channel1', 'channel2'],
            [],
            false,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($subscribeOp);
        
        // Then unsubscribe from one channel
        $unsubscribeOp = new UnsubscribeOperation();
        $unsubscribeOp->setChannels(['channel1']);
        $this->stateManager->adaptUnsubscribeBuilder($unsubscribeOp);
        
        $channelList = $this->stateManager->prepareChannelList(false);
        
        $this->assertCount(1, $channelList);
        $this->assertContains('channel2', $channelList);
        $this->assertNotContains('channel1', $channelList);
    }

    public function testAdaptUnsubscribeBuilderRemovesChannelGroups()
    {
        // First subscribe
        $subscribeOp = new SubscribeOperation(
            [],
            ['group1', 'group2'],
            false,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($subscribeOp);
        
        // Then unsubscribe from one group
        $unsubscribeOp = new UnsubscribeOperation();
        $unsubscribeOp->setChannelGroups(['group1']);
        $this->stateManager->adaptUnsubscribeBuilder($unsubscribeOp);
        
        $groupList = $this->stateManager->prepareChannelGroupList(false);
        
        $this->assertCount(1, $groupList);
        $this->assertContains('group2', $groupList);
        $this->assertNotContains('group1', $groupList);
    }

    public function testAdaptUnsubscribeBuilderRemovesPresenceChannels()
    {
        // Subscribe with presence
        $subscribeOp = new SubscribeOperation(
            ['channel1', 'channel2'],
            [],
            true,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($subscribeOp);
        
        // Unsubscribe from channel1
        $unsubscribeOp = new UnsubscribeOperation();
        $unsubscribeOp->setChannels(['channel1']);
        $this->stateManager->adaptUnsubscribeBuilder($unsubscribeOp);
        
        $channelList = $this->stateManager->prepareChannelList(true);
        
        // Should have channel2 and channel2-pnpres, but not channel1 or channel1-pnpres
        $this->assertCount(2, $channelList);
        $this->assertContains('channel2', $channelList);
        $this->assertContains('channel2-pnpres', $channelList);
        $this->assertNotContains('channel1', $channelList);
        $this->assertNotContains('channel1-pnpres', $channelList);
    }

    public function testIsEmptyAfterUnsubscribingFromAll()
    {
        // Subscribe
        $subscribeOp = new SubscribeOperation(
            ['channel1'],
            ['group1'],
            false,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($subscribeOp);
        $this->assertFalse($this->stateManager->isEmpty());
        
        // Unsubscribe from all
        $unsubscribeOp = new UnsubscribeOperation();
        $unsubscribeOp->setChannels(['channel1']);
        $unsubscribeOp->setChannelGroups(['group1']);
        $this->stateManager->adaptUnsubscribeBuilder($unsubscribeOp);
        
        $this->assertTrue($this->stateManager->isEmpty());
    }

    public function testMultipleSubscribeOperations()
    {
        // First subscription
        $operation1 = new SubscribeOperation(
            ['channel1'],
            [],
            false,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($operation1);
        
        // Second subscription
        $operation2 = new SubscribeOperation(
            ['channel2'],
            [],
            false,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($operation2);
        
        $channelList = $this->stateManager->prepareChannelList(false);
        
        $this->assertCount(2, $channelList);
        $this->assertContains('channel1', $channelList);
        $this->assertContains('channel2', $channelList);
    }

    public function testResubscribeToSameChannel()
    {
        // Subscribe to channel1
        $operation1 = new SubscribeOperation(
            ['channel1'],
            [],
            false,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($operation1);
        
        // Subscribe again to channel1 (should overwrite, not duplicate)
        $operation2 = new SubscribeOperation(
            ['channel1'],
            [],
            false,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($operation2);
        
        $channelList = $this->stateManager->prepareChannelList(false);
        
        $this->assertCount(1, $channelList);
        $this->assertContains('channel1', $channelList);
    }

    public function testEmptyChannelListWhenNoChannels()
    {
        $operation = new SubscribeOperation(
            [],
            ['group1'],
            false,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($operation);
        
        $channelList = $this->stateManager->prepareChannelList(false);
        
        $this->assertEmpty($channelList);
    }

    public function testEmptyGroupListWhenNoGroups()
    {
        $operation = new SubscribeOperation(
            ['channel1'],
            [],
            false,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($operation);
        
        $groupList = $this->stateManager->prepareChannelGroupList(false);
        
        $this->assertEmpty($groupList);
    }

    public function testUnsubscribeFromNonExistentChannel()
    {
        $operation = new SubscribeOperation(
            ['channel1'],
            [],
            false,
            null
        );
        $this->stateManager->adaptSubscribeBuilder($operation);
        
        // Unsubscribe from channel that was never subscribed
        $unsubscribeOp = new UnsubscribeOperation();
        $unsubscribeOp->setChannels(['channel2']);
        $this->stateManager->adaptUnsubscribeBuilder($unsubscribeOp);
        
        $channelList = $this->stateManager->prepareChannelList(false);
        
        // channel1 should still be there
        $this->assertCount(1, $channelList);
        $this->assertContains('channel1', $channelList);
    }
}
