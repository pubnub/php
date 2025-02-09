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

print("We're setting the channel's $channel additional info.\n");
print("\tTo exit type '/exit'\n");
print("\tTo show the current object type '/show'\n");
print("\tTo show this help type '/help'\n");

print("Enter the channel name: ");
$name = trim(fgets(STDIN));

print("Enter the channel description: ");
$description = trim(fgets(STDIN));

// Set channel metadata
$pubnub->setChannelMetadata()
    ->channel($channel)
    ->meta([
        "name" => $name,
        "description" => $description,
    ])
    ->sync();

print("The channel has been created with name and description.\n");

while (true) {
    // Fetch current channel metadata
    $response = $pubnub->getChannelMetadata()
        ->channel($channel)
        ->sync();

    print("Enter the field name: ");
    $fieldName = trim(fgets(STDIN));

    if ($fieldName === '/exit') {
        exit();
    } elseif ($fieldName === '/show') {
        print_r($response);
        continue;
    } elseif ($fieldName === '/help') {
        print("\tTo exit type '/exit'\n");
        print("\tTo show the current object type '/show'\n");
        print("\tTo show this help type '/help'\n");
        continue;
    }

    print("Enter the field value: ");
    $fieldValue = trim(fgets(STDIN));

    // Prepare custom fields
    $custom = (array)$response->getCustom();

    if (isset($custom[$fieldName])) {
        print("Field $fieldName already has a value. Overwrite? (y/n): ");
        $confirmation = trim(fgets(STDIN));

        if (strtolower($confirmation) !== 'y') {
            print("Object will not be updated.\n");
            continue;
        }
    }

    // Update custom field
    $custom[$fieldName] = $fieldValue;

    // Writing the updated object back to the server
    $pubnub->setChannelMetadata()
        ->channel($channel)
        ->meta([
            "name" => $response->getName(),
            "description" => $response->getDescription(),
            "custom" => $custom,
        ])
        ->sync();
    print("Object has been updated.\n");
}
