<?php

namespace PubNubTests\unit;

use PHPUnit\Framework\TestCase;
use PubNub\Builders\DTO\SubscribeOperation;
use PubNub\Managers\StateManager;
use PubNub\PNConfiguration;
use PubNub\PubNub;

class StateManagerTest extends TestCase
{
    private PubNub $pubnub;
    private StateManager $stateManager;

    protected function setUp(): void
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');

        $this->pubnub = new PubNub($config);
        $this->stateManager = new StateManager($this->pubnub);
    }

    public function testStateManagerDeduplicatesChannelListWithDuplicates(): void
    {
        // Subscribe with duplicate channels in a single operation
        $operation = new SubscribeOperation(['ch1', 'ch1', 'ch2'], [], false, 0);

        $this->stateManager->adaptSubscribeBuilder($operation);

        // Get the prepared channel list
        $channels = $this->stateManager->prepareChannelList(false);

        // Should be deduplicated - only unique channels
        $this->assertCount(2, $channels, 'StateManager should deduplicate channels');
        $this->assertContains('ch1', $channels);
        $this->assertContains('ch2', $channels);

        // Verify ch1 appears only once
        $ch1Count = count(array_filter($channels, fn($ch) => $ch === 'ch1'));
        $this->assertEquals(1, $ch1Count, 'Channel ch1 should appear only once');
    }

    public function testStateManagerDeduplicatesWhenSubscribingToSameChannelMultipleTimes(): void
    {
        // First subscription to ch1
        $operation1 = new SubscribeOperation(['ch1'], [], false, 0);
        $this->stateManager->adaptSubscribeBuilder($operation1);

        // Second subscription to ch1 and ch2
        $operation2 = new SubscribeOperation(['ch1', 'ch2'], [], false, 0);
        $this->stateManager->adaptSubscribeBuilder($operation2);

        // Get the prepared channel list
        $channels = $this->stateManager->prepareChannelList(false);

        // Should be deduplicated - only unique channels
        $this->assertCount(2, $channels, 'StateManager should deduplicate channels across multiple subscriptions');
        $this->assertContains('ch1', $channels);
        $this->assertContains('ch2', $channels);

        // Verify ch1 appears only once
        $ch1Count = count(array_filter($channels, fn($ch) => $ch === 'ch1'));
        $this->assertEquals(1, $ch1Count, 'Channel ch1 should appear only once even after multiple subscriptions');
    }

    public function testStateManagerDeduplicatesChannelGroups(): void
    {
        // Subscribe with duplicate channel groups
        $operation = new SubscribeOperation([], ['cg1', 'cg1', 'cg2'], false, 0);

        $this->stateManager->adaptSubscribeBuilder($operation);

        // Get the prepared channel group list
        $channelGroups = $this->stateManager->prepareChannelGroupList(false);

        // Should be deduplicated
        $this->assertCount(2, $channelGroups, 'StateManager should deduplicate channel groups');
        $this->assertContains('cg1', $channelGroups);
        $this->assertContains('cg2', $channelGroups);

        // Verify cg1 appears only once
        $cg1Count = count(array_filter($channelGroups, fn($cg) => $cg === 'cg1'));
        $this->assertEquals(1, $cg1Count, 'Channel group cg1 should appear only once');
    }
}
