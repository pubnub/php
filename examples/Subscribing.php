<?php

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
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

// snippet.subscribe_single_channel
$pubnub->subscribe()
    ->channels("my_channel")
    ->execute();
// snippet.end

// Disable for "one class per file" rule
// phpcs:disable
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
// phpcs:enable

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

// Disable for the "declare symbols vs side effects" rule
// phpcs:disable
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
// phpcs:enable

// snippet.basic_subscribe_with_logging
use Monolog\Handler\ErrorLogHandler;

$pnconf = new PNConfiguration();

$pnconf->setPublishKey("demo");
$pnconf->setSubscribeKey("demo");
$pnconf->setUserId("php-subscriber-with-logging");

$pubnub = new PubNub($pnconf);

$pubnub->getLogger()->pushHandler(new ErrorLogHandler());

$pubnub->subscribe()->channels("my_channel")->execute();
// snippet.end

// Disable for the "one class per file" rule
// phpcs:disable
// snippet.subscribe_with_state
class MySubscribeCallbackWithState extends SubscribeCallback
{
    public function status($pubnub, $status)
    {
        if ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            $result = $pubnub
                ->setState()
                ->channels("awesomeChannel")
                ->channelGroups("awesomeChannelGroup")
                ->state([
                    "fieldA" => "awesome",
                    "fieldB" => 10,
                ])
                ->sync();
            print_r($result);
        }
    }

    public function message($pubnub, $message)
    {
    }

    public function presence($pubnub, $presence)
    {
    }
}

$subscribeCallback = new MySubscribeCallbackWithState();

$pubnub->addListener($subscribeCallback);

$pubnub->subscribe()
    ->channels("my_channel")
    ->execute();
// snippet.end
// phpcs:enable

// Disable for the "one class per file" rule
// phpcs:disable
// snippet.unsubscribe_from_channel
use PubNub\Exceptions\PubNubUnsubscribeException;

class MyUnsubscribeCallback extends SubscribeCallback
{
    public function status($pubnub, $status)
    {
        if ($this->checkUnsubscribeCondition()) {
            throw (new PubNubUnsubscribeException())->setChannels("awesomeChannel");
        }
    }

    public function message($pubnub, $message)
    {
    }

    public function presence($pubnub, $presence)
    {
    }

    public function checkUnsubscribeCondition()
    {
        // return true or false
        return false;
    }
}

$pnconfig = new PNConfiguration();

$pnconfig->setPublishKey("demo");
$pnconfig->setSubscribeKey("demo");
$pnconfig->setUserId("php-unsubscribe-demo");

$pubnub = new PubNub($pnconfig);

$subscribeCallback = new MyUnsubscribeCallback();

$pubnub->addListener($subscribeCallback);

$pubnub->subscribe()
    ->channels("awesomeChannel")
    ->execute();
// snippet.end
// phpcs:enable

// snippet.subscribe_multiple_channels
$pubnub->subscribe()
    ->channels(["my_channel1", "my_channel2"])
    ->execute();
// snippet.end

// snippet.subscribe_presence_channel
$pubnub->subscribe()
    ->channels("my_channel")
    ->withPresence()
    ->execute();
// snippet.end

// snippet.wildcard_subscribe
$pubnub->subscribe()
    ->channels("foo.*")
    ->execute();
// snippet.end

// snippet.subscribe_channel_group
$pubnub->subscribe()
    ->channelGroups(["cg1", "cg2"])
    ->execute();
// snippet.end

// snippet.subscribe_presence_channel_group
$pubnub->subscribe()
    ->channelGroups("awesome_channel_group")
    ->withPresence()
    ->execute();
// snippet.end

//Disabling code sniffer for whole snippet to not include single-line disable in docs
// phpcs:disable
// snippet.unsubscribe_multiple_channels
class MyUnsubscribeMultipleCallback extends SubscribeCallback
{
    public function status($pubnub, $status)
    {
        if ($this->checkUnsubscribeCondition()) {
            throw (new PubNubUnsubscribeException())->setChannels(["channel1", "channel2", "channel3"]);
        }
    }

    public function message($pubnub, $message)
    {
    }

    public function presence($pubnub, $presence)
    {
    }

    public function checkUnsubscribeCondition()
    {
        return false;
    }
}
// snippet.end
//phpcs:enable

//Disabling code sniffer for whole snippet to not include single-line disable in docs
// phpcs:disable
// snippet.unsubscribe_channel_group
class MyUnsubscribeChannelGroupCallback extends SubscribeCallback
{
    public function status($pubnub, $status)
    {
        if ($this->checkUnsubscribeCondition()) {
            throw (new PubNubUnsubscribeException())->setChannelGroups(["cg1", "cg2"]);
        }
    }

    public function message($pubnub, $message)
    {
    }

    public function presence($pubnub, $presence)
    {
    }

    public function checkUnsubscribeCondition()
    {
        return false;
    }
}
// snippet.end
// phpcs:enable

// Only run the main loop if not being included by tests
if (!defined('PHPUNIT_RUNNING')) {
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
}
