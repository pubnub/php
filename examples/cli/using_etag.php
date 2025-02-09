<?php

require_once 'vendor/autoload.php';

use PubNub\Exceptions\PubNubServerException;
use PubNub\PubNub;
use PubNub\PNConfiguration;

$config = new PNConfiguration();
$config->setPublishKey('demo');
$config->setSubscribeKey('demo');
$config->setUuid("example");

$config_2 = clone $config;
$config_2->setUuid("example_2");

$pubnub = new PubNub($config);
$pubnub_2 = new PubNub($config_2);

$sample_user = [
    "uuid" => "SampleUser",
    "name" => "John Doe",
    "email" => "jd@example.com",
    "custom" => ["age" => 42, "address" => "123 Main St."]
];

// One client creates a metadata for the user "SampleUser" and successfully writes it to the server.
$set_result = $pubnub->setUUIDMetadata()->uuid("SampleUser")->meta($sample_user)->sync();

// We store the eTag for the user for further updates.
$original_e_tag = $set_result->getETag();

print("We receive the eTag for the user: $original_e_tag" . PHP_EOL);

// Another client sets the user meta with the same UUID but different data.
$overwrite_result = $pubnub_2->setUUIDMetadata()->uuid("SampleUser")->meta(["name" => "Jane Doe"])->sync();
$new_e_tag = $overwrite_result->getETag();

// We can verify that there is a new eTag for the user.
print(
    "After overwrite there's a new eTag: $original_e_tag === $new_e_tag? "
    . ($original_e_tag === $new_e_tag ? "true" : "false") . PHP_EOL
);

// We modify the user and try to update it.
$updated_user = array_merge($sample_user, ["custom" => ["age" => 43, "address" => "321 Other St."]]);

try {
    $update_result = $pubnub->setUUIDMetadata()
        ->uuid("SampleUser")
        ->meta($updated_user)
        ->ifMatchesETag($original_e_tag)
        ->sync();
    print_r($update_result);
} catch (PubNubServerException $e) {
    if ($e->getStatusCode() === 412) {
        print(
            "Update failed because eTag mismatch: " . $e->getServerErrorMessage()
            . "\nHTTP Status Code: " . $e->getStatusCode() . PHP_EOL
        );
    } else {
        print( "Unexpected error: " . $e->getMessage() . PHP_EOL);
    }
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . PHP_EOL;
}
