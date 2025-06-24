<?php

namespace PubNub\Demo;

require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Enums\PNStatusCategory;

// snippet.config
// Initialize PubNub configuration
$pnconfig = new PNConfiguration();
$pnconfig->setPublishKey("demo");  // Replace with your publish key
$pnconfig->setSubscribeKey("demo"); // Replace with your subscribe key
$pnconfig->setUserId("php-subscriber-" . uniqid()); // Unique user ID for this demo
// snippet.end

// snippet.init
// Create PubNub instance
$pubnub = new PubNub($pnconfig);
// snippet.end

// snippet.callback
// Create a custom callback class to handle messages and status updates
class MySubscribeCallback extends SubscribeCallback
{
    public function status($pubnub, $status)
    {
        switch ($status->getCategory()) {
            case PNStatusCategory::PNConnectedCategory:
                echo "Connected to PubNub!\n";
                break;
            case PNStatusCategory::PNReconnectedCategory:
                echo "Reconnected to PubNub!\n";
                break;
            case PNStatusCategory::PNDisconnectedCategory:
                echo "Disconnected from PubNub!\n";
                break;
            case PNStatusCategory::PNUnexpectedDisconnectCategory:
                echo "Unexpectedly disconnected from PubNub!\n";
                break;
        }
    }

    public function message($pubnub, $message)
    {
        echo "Received message: " . json_encode($message->getMessage()) . "\n";
        echo "Channel: " . $message->getChannel() . "\n";
        echo "Publisher: " . $message->getPublisher() . "\n";
        echo "Timetoken: " . $message->getTimetoken() . "\n";
    }

    public function presence($pubnub, $presence)
    {
        echo "Presence event: " . $presence->getEvent() . "\n";
        echo "Channel: " . $presence->getChannel() . "\n";
        echo "UUID: " . $presence->getUuid() . "\n";
        echo "Occupancy: " . $presence->getOccupancy() . "\n";
    }
}
// snippet.end

// snippet.subscribe
// Add the callback to PubNub
$pubnub->addListener(new MySubscribeCallback());

// Subscribe to multiple channels concurrently
$channels = ["demo_channel", "demo_channel2", "demo_channel3"];

foreach ($channels as $channel) {
    $pubnub->subscribe()
        ->channels($channel)
        ->withPresence()
        ->execute();

    echo "Subscribed to channel: $channel\n";
}
// snippet.end

// snippet.history
// Get message history
function getHistory($pubnub, $channels)
{
    $channel = $channels[array_rand($channels)];
    try {
        $result = $pubnub->history()
            ->channel($channel)
            ->count(5)
            ->sync();

        echo "Message history for $channel:\n";
        foreach ($result->getMessages() as $message) {
            echo json_encode($message, JSON_PRETTY_PRINT) . "\n";
        }
    } catch (\Exception $e) {
        echo "Error getting history: " . $e->getMessage() . "\n";
    }
}
// snippet.end

echo "Starting PubNub Subscriber...\n";
echo "Press Ctrl+C to exit\n";

// Main loop
$lastHistoryTime = 0;

while (true) {
    $currentTime = time();

    // Check history every 15 seconds
    if ($currentTime - $lastHistoryTime >= 15) {
        getHistory($pubnub, $channels);
        $lastHistoryTime = $currentTime;
    }

    // Small sleep to prevent CPU overuse
    usleep(100000); // 100ms
}
