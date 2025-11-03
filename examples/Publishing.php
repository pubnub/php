<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubException;

// Initialize PubNub with demo keys
$pnconfig = new PNConfiguration();
$pnconfig->setPublishKey("demo");
$pnconfig->setSubscribeKey("demo");
$pnconfig->setUserId("php-publish-demo-user");

$pubnub = new PubNub($pnconfig);

// snippet.basic_publish
$result = $pubnub->publish()
    ->channel("my_channel")
    ->message(["text" => "Hello World!"])
    ->sync();
assert($result->getTimetoken() > 0);
echo "Basic publish timetoken: {$result->getTimetoken()}\n";
// snippet.end

// snippet.basic_publish_too_long_message
try {
    $result = $pubnub->publish()
        ->channel("my_channel")
        ->message(["text" => "Hello World!", "description" => str_repeat("Get allows slightly shorter messages", 1000)])
        ->sync();
    echo "Basic publish timetoken: {$result->getTimetoken()}\n";
} catch (PubNubException $e) {
    $status = $e->getStatus();
    assert($status->isError() == true);
    assert($status->getStatusCode() == '414');
    print("Publish failed: {$status->getException()->getBody()->message}\n");
}
// snippet.end

// snippet.publish_with_meta
$result = $pubnub->publish()
    ->channel("my_channel")
    ->message(["text" => "Message with meta"])
    ->meta(["custom" => "metadata"])
    ->sync();
assert($result->getTimetoken() > 0);
echo "Publish with meta timetoken: {$result->getTimetoken()}\n";
// snippet.end

// snippet.publish_with_ttl
$result = $pubnub->publish()
    ->channel("my_channel")
    ->message(["text" => "Message with TTL"])
    ->shouldStore(true)
    ->ttl(24) // Store for 24 hours
    ->sync();

echo "Publish with TTL timetoken: {$result->getTimetoken()}\n";
// snippet.end

// snippet.publish_with_custom_type
$result = $pubnub->publish()
    ->channel("my_channel")
    ->message(["text" => "Message with custom type"])
    ->customMessageType("text")
    ->sync();
assert($result->getTimetoken() > 0);
echo "Publish with custom type timetoken: {$result->getTimetoken()}\n";
// snippet.end

// snippet.publish_with_post
$result = $pubnub->publish()
    ->channel("my_channel")
    ->message([
        "text" => "Message using POST",
        "description" => str_repeat("Post allows to publish longer messages", 750)
    ])
    ->usePost(true)
    ->sync();
assert($result->getTimetoken() > 0);
echo "Publish with POST timetoken: {$result->getTimetoken()}\n";
// snippet.end

// snippet.publish_too_long_message_with_post
try {
    $result = $pubnub->publish()
        ->channel("my_channel")
        ->message([
            "text" => "Message using POST",
            "description" => str_repeat("Post allows to publish longer messages", 1410)
        ])
        ->usePost(true)
        ->sync();
    assert($result->getTimetoken() > 0);
    echo "Publish with POST timetoken: {$result->getTimetoken()}\n";
} catch (PubNubException $e) {
    $status = $e->getStatus();
    assert($status->isError() == true);
    assert($status->getStatusCode() == 413);
    print("Publish failed: {$status->getException()->getBody()->message}\n");
}
// snippet.end

// snippet.fire
$result = $pubnub->fire()
    ->channel("my_channel")
    ->message(["text" => "Fire message"])
    ->sync();
assert($result->getTimetoken() > 0);
echo "Fire timetoken: {$result->getTimetoken()}\n";
// snippet.end

// snippet.signal
$result = $pubnub->signal()
    ->channel("my_channel")
    ->message(["text" => "Signal message"])
    ->sync();
assert($result->getTimetoken() > 0);
echo "Signal timetoken: {$result->getTimetoken()}\n";
// snippet.end

// snippet.publish_array
try {
    $result = $pubnub->publish()
        ->channel("my_channel")
        ->message(["hello", "there"])
        ->meta(["name" => "Alex", "online" => true])
        ->sync();
    print_r($result->getTimetoken());
} catch (PubNubException $error) {
    echo "Error: " . $error->getMessage() . "\n";
}
// snippet.end
