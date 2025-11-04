<?php

// This file contains additional code snippets for documentation examples
require_once(__DIR__ . '/../vendor/autoload.php');

use PubNub\PubNub;
use PubNub\PNConfiguration;

$config = new PNConfiguration();
$config->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?: 'demo');
$config->setPublishKey(getenv('PUBLISH_KEY') ?: 'demo');
$config->setSecretKey(getenv('SECRET_KEY') ?: 'demo');
$config->setUserId('snippets-demo');
$pubnub = new PubNub($config);

// MESSAGE PERSISTENCE SNIPPETS

// snippet.history_newer_than_timetoken
$pubnub->history()
    ->channel("my_channel")
    ->start(13847168620721752)
    ->reverse(true)
    ->sync();
// snippet.end

// snippet.history_until_timetoken
$pubnub->history()
    ->channel("my_channel")
    ->count(100)
    ->start(-1)
    ->end(13847168819178600)
    ->reverse(true)
    ->sync();
// snippet.end

// snippet.history_include_timetoken
$pubnub->history()
    ->channel("my_channel")
    ->count(5)
    ->includeTimetoken(true)
    ->sync();
// snippet.end

// snippet.delete_specific_message
$pubnub->deleteMessages()
    ->channel("my_channel")
    ->start(15957709532217050)
    ->end(15957709532217050)
    ->sync();
// snippet.end

// snippet.message_counts_different_timetokens
$pubnub->messageCounts()
    ->channels(["my_channel1", "my_channel2"])
    ->channelsTimetoken([
        "my_channel1" => 15614559062344052,
        "my_channel2" => 15614559062344053
    ])
    ->sync();
// snippet.end

// PRESENCE SNIPPETS

// snippet.here_now_with_state
$result = $pubnub->hereNow()
    ->channels("my_channel")
    ->includeUuids(true)
    ->includeState(true)
    ->sync();
// snippet.end

// snippet.here_now_occupancy_only
$result = $pubnub->hereNow()
    ->channels("my_channel")
    ->includeUuids(false)
    ->includeState(false)
    ->sync();
// snippet.end

// snippet.here_now_channel_groups
$pubnub->hereNow()
    ->channelGroups(["cg1", "cg2", "cg3"])
    ->includeUuids(true)
    ->includeState(true)
    ->sync();
// snippet.end

// snippet.where_now
$result = $pubnub->whereNow()
    ->sync();
// snippet.end

// snippet.where_now_specific_uuid
$result = $pubnub->whereNow()
    ->uuid("his-uuid")
    ->sync();
// snippet.end

// snippet.set_state
$pubnub->setState()
    ->channels(["ch1", "ch2", "ch3"])
    ->state(["age" => 30])
    ->sync();
// snippet.end

// snippet.get_state
$pubnub->getState()
    ->channels(["ch1", "ch2", "ch3"])
    ->sync();
// snippet.end

// snippet.set_state_channel_group
$pubnub->setState()
    ->channelGroups(["cg1", "cg2", "cg3"])
    ->state(["age" => 30])
    ->sync();
// snippet.end

// ACCESS MANAGER V3 SNIPPETS

// snippet.grant_different_access_levels
$pubnub->grantToken()
    ->ttl(15)
    ->authorizedUuid('my-authorized-uuid')
    ->addChannelResources([
        'channel-a' => ['read' => true],
        'channel-b' => ['read' => true, 'write' => true],
        'channel-c' => ['read' => true, 'write' => true],
        'channel-d' => ['read' => true, 'write' => true],
    ])
    ->addChannelGroupResources([
        'channel-group-b' => ['read' => true],
    ])
    ->addUuidResources([
        'uuid-c' => ['get' => true],
        'uuid-d' => ['get' => true, 'update' => true],
    ])
    ->sync();
// snippet.end

// snippet.grant_regex_channels
$pubnub->grantToken()
    ->ttl(15)
    ->authorizedUuid('my-authorized-uuid')
    ->addChannelPatterns([
        '^channel-[A-Za-z0-9]$' => ['read' => true],
    ])
    ->sync();
// snippet.end

// snippet.grant_combined_regex
$pubnub->grantToken()
    ->ttl(15)
    ->authorizedUuid('my-authorized-uuid')
    ->addChannelResources([
        'channel-a' => ['read' => true],
        'channel-b' => ['read' => true, 'write' => true],
        'channel-c' => ['read' => true, 'write' => true],
        'channel-d' => ['read' => true, 'write' => true],
    ])
    ->addChannelGroupResources([
        'channel-group-b' => ['read' => true],
    ])
    ->addUuidResources([
        'uuid-c' => ['get' => true],
        'uuid-d' => ['get' => true, 'update' => true],
    ])
    ->addChannelPatterns([
        '^channel-[A-Za-z0-9]$' => ['read' => true],
    ]);
// snippet.end

// Disabling phpcs to keep token as one line
// phpcs:disable
// snippet.permissions_object_example
$pubnub->parseToken("p0F2AkF0Gl2BEIJDdHRsGGRDcmVzpERjaGFuoENncnCgQ3VzcqBDc3BjoENwYXSkRGNoYW6gQ2dycKBDdXNyomZeZW1wLSoDZl5tZ3ItKhgbQ3NwY6JpXnB1YmxpYy0qA2pecHJpdmF0ZS0qGBtEbWV0YaBDc2lnWCAsvzGmd2rcgtr9rcs4r2tqC87YSppSYqs9CKfaM5IRZA")
    ->getChannelResource('my-channel');
// snippet.end
// phpcs:enable
