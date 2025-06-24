<?php

/**
 * PubNub Message Persistence Demo Application
 *
 * This demo showcases all Message Persistence methods from the PubNub PHP SDK:
 * - Fetch History (fetchMessages)
 * - History (history)
 * - Delete Messages from History (deleteMessages)
 * - Message Counts (messageCounts)
 *
 * Based on: https://www.pubnub.com/docs/sdks/php/api-reference/storage-and-playback
 */

namespace PubNub\Examples;

// Include Composer autoloader (adjust path if needed)
require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubException;

class MessagePersistenceDemo
{
    private $pubnub;
    private $demoChannel = "message-persistence-demo-channel";
    private $multiChannel1 = "demo-channel-1";
    private $multiChannel2 = "demo-channel-2";
    private $publishedTimetokens = [];

    public function __construct()
    {
        // Create configuration with demo keys
        $pnConfig = new PNConfiguration();
        $pnConfig->setPublishKey("demo");
        $pnConfig->setSubscribeKey("demo");
        $pnConfig->setSecretKey("demo"); // Required for delete operations
        $pnConfig->setUserId("message-persistence-demo-user");

        $this->pubnub = new PubNub($pnConfig);

        echo "=== PubNub Message Persistence Demo ===\n\n";
    }

    /**
     * Step 1: Publish sample messages to have data for persistence operations
     */
    public function publishSampleMessages()
    {
        echo "📤 Publishing sample messages...\n";

        $messages = [
            ["content" => "First demo message", "type" => "text", "timestamp" => time()],
            ["content" => "Second demo message", "type" => "text", "timestamp" => time() + 1],
            ["content" => "Third demo message with metadata", "type" => "rich", "timestamp" => time() + 2],
            ["content" => "Fourth message for testing", "type" => "text", "timestamp" => time() + 3],
            ["content" => "Fifth and final message", "type" => "text", "timestamp" => time() + 4]
        ];

        try {
            // Publish to main demo channel
            foreach ($messages as $index => $message) {
                $result = $this->pubnub->publish()
                    ->channel($this->demoChannel)
                    ->message($message)
                    ->meta(["messageIndex" => $index + 1, "demo" => true])
                    ->sync();

                $this->publishedTimetokens[] = $result->getTimetoken();
                echo "  ✓ Published message " . ($index + 1) . " - Timetoken: " . $result->getTimetoken() . "\n";

                // Small delay to ensure different timetokens
                usleep(100000); // 0.1 seconds
            }

            // Publish to multiple channels for multi-channel demos
            $this->pubnub->publish()
                ->channel($this->multiChannel1)
                ->message(["content" => "Message for channel 1", "channel" => 1])
                ->sync();

            $this->pubnub->publish()
                ->channel($this->multiChannel2)
                ->message(["content" => "Message for channel 2", "channel" => 2])
                ->sync();

            echo "  ✓ Published messages to multiple channels\n\n";

            // Wait a bit for messages to be stored
            sleep(2);
        } catch (PubNubException $e) {
            echo "❌ Error publishing messages: " . $e->getMessage() . "\n\n";
        }
    }

    /**
     * Demo 1: Fetch History (fetchMessages) - Basic Usage
     */
    public function demoFetchHistoryBasic()
    {
        echo "📜 Demo 1: Fetch History - Basic Usage\n";
        echo "Retrieving last messages from channel...\n";

        try {
            $result = $this->pubnub->fetchMessages()
                ->channels($this->demoChannel)
                ->count(5)
                ->sync();

            echo "  📊 Results:\n";
            echo "  - Start Timetoken: " . $result->getStartTimetoken() . "\n";
            echo "  - End Timetoken: " . $result->getEndTimetoken() . "\n";

            $channels = $result->getChannels();
            if (isset($channels[$this->demoChannel])) {
                $messages = $channels[$this->demoChannel];
                echo "  - Message Count: " . count($messages) . "\n";

                foreach ($messages as $index => $message) {
                    echo "    Message " . ($index + 1) . ":\n";
                    echo "      Content: " . json_encode($message->getMessage()) . "\n";
                    echo "      Timetoken: " . $message->getTimetoken() . "\n";
                    if ($message->getMetadata()) {
                        echo "      Meta: " . json_encode($message->getMetadata()) . "\n";
                    }
                    echo "\n";
                }
            }
        } catch (PubNubException $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    /**
     * Demo 2: Fetch History with Advanced Options
     */
    public function demoFetchHistoryAdvanced()
    {
        echo "📜 Demo 2: Fetch History - Advanced Options\n";
        echo "Retrieving messages with metadata, message type, and UUID info...\n";

        try {
            $result = $this->pubnub->fetchMessages()
                ->channels($this->demoChannel)
                ->includeMeta(true)
                ->includeMessageType(true)
                ->includeUuid(true)
                ->sync();

            echo "  📊 Advanced Results:\n";
            $channels = $result->getChannels();
            if (isset($channels[$this->demoChannel])) {
                $messages = $channels[$this->demoChannel];

                foreach ($messages as $index => $message) {
                    echo "    Advanced Message " . ($index + 1) . ":\n";
                    echo "      Content: " . json_encode($message->getMessage()) . "\n";
                    echo "      Timetoken: " . $message->getTimetoken() . "\n";
                    echo "      UUID: " . ($message->getUuid() ?? 'N/A') . "\n";
                    echo "      Message Type: " . ($message->getMessageType() ?? 'N/A') . "\n";
                    echo "      Meta: " . json_encode($message->getMetadata()) . "\n";
                    echo "\n";
                }
            }
        } catch (PubNubException $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    /**
     * Demo 3: Fetch History from Multiple Channels
     */
    public function demoFetchHistoryMultiChannel()
    {
        echo "📜 Demo 3: Fetch History - Multiple Channels\n";
        echo "Retrieving messages from multiple channels...\n";

        try {
            $result = $this->pubnub->fetchMessages()
                ->channels([$this->multiChannel1, $this->multiChannel2])
                ->includeMeta(true)
                ->sync();

            echo "  📊 Multi-Channel Results:\n";
            $channels = $result->getChannels();

            foreach ($channels as $channelName => $messages) {
                echo "    Channel: $channelName\n";
                echo "    Message Count: " . count($messages) . "\n";

                foreach ($messages as $index => $message) {
                    echo "      Message " . ($index + 1) . ": " . json_encode($message->getMessage()) . "\n";
                }
                echo "\n";
            }
        } catch (PubNubException $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    /**
     * Demo 4: History Method - Basic Usage
     */
    public function demoHistoryBasic()
    {
        echo "📚 Demo 4: History Method - Basic Usage\n";
        echo "Using history() method to retrieve messages...\n";

        try {
            $result = $this->pubnub->history()
                ->channel($this->demoChannel)
                ->count(5)
                ->includeTimetoken(true)
                ->sync();

            echo "  📊 History Results:\n";
            echo "  - Start Timetoken: " . $result->getStartTimetoken() . "\n";
            echo "  - End Timetoken: " . $result->getEndTimetoken() . "\n";

            $messages = $result->getMessages();
            echo "  - Message Count: " . count($messages) . "\n";

            foreach ($messages as $index => $message) {
                echo "    Message " . ($index + 1) . ":\n";
                echo "      Content: " . json_encode($message->getEntry()) . "\n";
                echo "      Timetoken: " . $message->getTimetoken() . "\n";
                echo "\n";
            }
        } catch (PubNubException $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    /**
     * Demo 5: History Method - Reverse Order (Oldest First)
     */
    public function demoHistoryReverse()
    {
        echo "📚 Demo 5: History Method - Reverse Order (Oldest First)\n";
        echo "Retrieving oldest 3 messages first...\n";

        try {
            $result = $this->pubnub->history()
                ->channel($this->demoChannel)
                ->count(3)
                ->reverse(true)
                ->includeTimetoken(true)
                ->sync();

            echo "  📊 Reverse History Results:\n";
            $messages = $result->getMessages();

            foreach ($messages as $index => $message) {
                echo "    Oldest Message " . ($index + 1) . ":\n";
                echo "      Content: " . json_encode($message->getEntry()) . "\n";
                echo "      Timetoken: " . $message->getTimetoken() . "\n";
                echo "\n";
            }
        } catch (PubNubException $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    /**
     * Demo 6: History Method - Time Range
     */
    public function demoHistoryTimeRange()
    {
        echo "📚 Demo 6: History Method - Time Range\n";

        if (count($this->publishedTimetokens) >= 2) {
            $startTime = $this->publishedTimetokens[0];
            $endTime = $this->publishedTimetokens[2]; // Get messages between first and third

            echo "Retrieving messages between timetoken $startTime and $endTime...\n";

            try {
                $result = $this->pubnub->history()
                    ->channel($this->demoChannel)
                    ->start($startTime)
                    ->end($endTime)
                    ->includeTimetoken(true)
                    ->sync();

                echo "  📊 Time Range Results:\n";
                $messages = $result->getMessages();
                echo "  - Message Count: " . count($messages) . "\n";

                foreach ($messages as $index => $message) {
                    echo "    Message " . ($index + 1) . ":\n";
                    echo "      Content: " . json_encode($message->getEntry()) . "\n";
                    echo "      Timetoken: " . $message->getTimetoken() . "\n";
                    echo "\n";
                }
            } catch (PubNubException $e) {
                echo "❌ Error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  ⚠️  Not enough published messages for time range demo\n";
        }

        echo "\n";
    }

    /**
     * Demo 7: Message Counts
     */
    public function demoMessageCounts()
    {
        echo "🔢 Demo 7: Message Counts\n";
        echo "Getting message counts for channels...\n";

        try {
            // Get the oldest timetoken for counting
            $oldestTimetoken = !empty($this->publishedTimetokens) ?
                min($this->publishedTimetokens) : "0";

            $result = $this->pubnub->messageCounts()
                ->channels([$this->demoChannel])
                ->channelsTimetoken([$oldestTimetoken])
                ->sync();

            echo "  📊 Message Count Results:\n";
            $channels = $result->getChannels();

            foreach ($channels as $channelName => $count) {
                echo "    Channel '$channelName': $count messages\n";
            }
        } catch (PubNubException $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    /**
     * Demo 8: Message Counts - Multiple Channels
     */
    public function demoMessageCountsMultiple()
    {
        echo "🔢 Demo 8: Message Counts - Multiple Channels\n";
        echo "Getting message counts for multiple channels...\n";

        try {
            $result = $this->pubnub->messageCounts()
                ->channels([$this->demoChannel, $this->multiChannel1, $this->multiChannel2])
                ->channelsTimetoken(["0"]) // Single timetoken for all channels
                ->sync();

            echo "  📊 Multiple Channel Count Results:\n";
            $channels = $result->getChannels();

            foreach ($channels as $channelName => $count) {
                echo "    Channel '$channelName': $count messages\n";
            }
        } catch (PubNubException $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    /**
     * Demo 9: Delete Messages from History
     * Note: This requires secret key and proper permissions
     */
    public function demoDeleteMessages()
    {
        echo "🗑️  Demo 9: Delete Messages from History\n";
        echo "Attempting to delete specific message from history...\n";

        if (!empty($this->publishedTimetokens)) {
            // Delete the first message by using its timetoken
            $targetTimetoken = $this->publishedTimetokens[0];
            $startTimetoken = $targetTimetoken - 1;
            $endTimetoken = $targetTimetoken;

            echo "  Deleting message with timetoken: $targetTimetoken\n";
            echo "  Using range: $startTimetoken to $endTimetoken\n";

            try {
                $this->pubnub->deleteMessages()
                    ->channel($this->demoChannel)
                    ->start($startTimetoken)
                    ->end($endTimetoken)
                    ->sync();

                echo "  ✅ Delete operation completed successfully\n";
                echo "  Note: Message deletion may take a moment to reflect in history\n";
            } catch (PubNubException $e) {
                echo "  ⚠️  Delete operation failed: " . $e->getMessage() . "\n";
                echo "  Note: Delete operations require proper key permissions and secret key\n";
            }
        } else {
            echo "  ⚠️  No published messages available for deletion demo\n";
        }

        echo "\n";
    }

    /**
     * Demo 10: Verify Deletion
     */
    public function demoVerifyDeletion()
    {
        echo "🔍 Demo 10: Verify Deletion\n";
        echo "Checking if message was deleted...\n";

        try {
            // Wait a moment for deletion to process
            sleep(2);

            $result = $this->pubnub->fetchMessages()
                ->channels($this->demoChannel)
                ->count(10)
                ->sync();

            echo "  📊 Current Message Count After Deletion:\n";
            $channels = $result->getChannels();
            if (isset($channels[$this->demoChannel])) {
                $messages = $channels[$this->demoChannel];
                echo "  - Messages remaining: " . count($messages) . "\n";

                // Check if the first timetoken is still present
                $remainingTimetokens = array_map(function ($msg) {
                    return $msg->getTimetoken();
                }, $messages);

                $deletedTimetoken = $this->publishedTimetokens[0] ?? null;
                if ($deletedTimetoken && !in_array($deletedTimetoken, $remainingTimetokens)) {
                    echo "  ✅ Message with timetoken $deletedTimetoken was successfully deleted\n";
                } else {
                    echo "  ⚠️  Message may still be present (deletion can take time to propagate)\n";
                }
            }
        } catch (PubNubException $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    /**
     * Run all demos
     */
    public function runAllDemos()
    {
        // Step 1: Setup data
        $this->publishSampleMessages();

        // Step 2: Fetch History demos
        $this->demoFetchHistoryBasic();
        $this->demoFetchHistoryAdvanced();
        $this->demoFetchHistoryMultiChannel();

        // Step 3: History method demos
        $this->demoHistoryBasic();
        $this->demoHistoryReverse();
        $this->demoHistoryTimeRange();

        // Step 4: Message count demos
        $this->demoMessageCounts();
        $this->demoMessageCountsMultiple();

        // Step 5: Delete operations (may require proper permissions)
        $this->demoDeleteMessages();
        $this->demoVerifyDeletion();

        echo "🎉 All Message Persistence demos completed!\n";
    }
}

// Run the demo
if (file_exists('vendor/autoload.php')) {
    $demo = new MessagePersistenceDemo();
    $demo->runAllDemos();
} else {
    echo "❌ Please run 'composer install' to install PubNub SDK dependencies first.\n";
    echo "Make sure you have composer.json with PubNub dependency configured.\n";
}
