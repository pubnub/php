
<?php

set_time_limit(0);

require('../../vendor/autoload.php');

use PubNub\PNConfiguration;
use PubNub\PubNub;

$pnconf = new PNConfiguration();
$pnconf->setPublishKey("demo");
$pnconf->setSubscribeKey("demo");
$pnconf->setUuid("example");

$pubnub = new PubNub($pnconf);

$channel = "demo_example";
$channelName = "Channel1on1";
$channelDescription = "Channel for 1on1 conversation";
$status = "active";
$type = "1on1";
$initialCustom = ["Days" => "Mon-Fri"];

// Set initial channel metadata
$pubnub->setChannelMetadata()
    ->channel($channel)
    ->meta(["custom" => $initialCustom])
    ->status($status)
    ->type($type)
    ->sync();

// Fetch the current metadata
$response = $pubnub->getChannelMetadata()
    ->channel($channel)
    ->includeCustom(true)
    ->sync();

$custom = (array)$response->getCustom();
$additionalMetadata = ["Months" => "Jan-May"];

// Merge additional metadata
$updatedCustomMetadata = array_merge($custom, $additionalMetadata);

// Update the channel metadata
$updatedMetadata = $pubnub->setChannelMetadata()
    ->channel($channel)
    ->custom($updatedCustomMetadata)
    ->includeCustom(true)
    ->sync();

print("Updated channel metadata:");
print_r($updatedMetadata->getData());

// Cleanup
$pubnub->removeChannelMetadata()
    ->channel($channel)
    ->sync();
