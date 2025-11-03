<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubServerException;

// snippet.setup
// Create configuration
$pnConfig = new PNConfiguration();
$pnConfig->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo");
$pnConfig->setPublishKey(getenv("PUBLISH_KEY") ?? "demo");
$pnConfig->setUserId("php-channel-group-demo");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);
// snippet.end

// snippet.add_channels
$result = $pubnub->addChannelToChannelGroup()
    ->channels(["news", "sports"])
    ->channelGroup("my-group")
    ->sync();

echo "Channels added to group successfully!" . PHP_EOL;
// snippet.end

// snippet.list_channels
$result = $pubnub->listChannelsInChannelGroup()
    ->channelGroup("cg1")
    ->sync();
// snippet.end

// snippet.remove_channels
$pubnub->removeChannelFromChannelGroup()
    ->channels("son")
    ->channelGroup("family")
    ->sync();
// snippet.end

// snippet.delete_channel_group
$pubnub->removeChannelGroup()
    ->channelGroup("family")
    ->sync();
// snippet.end
