<?php

namespace PubNub\Examples;

// Include Composer autoloader (adjust path if needed)
require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubException;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Enums\PNStatusCategory;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\Consumer\PubSub\PNMessageActionResult;
use PubNub\Models\Consumer\MessageActions\PNMessageAction;

/**
 * PubNub Message Actions Comprehensive Example
 *
 * This example demonstrates all Message Actions features:
 * 1. Adding message actions (reactions, read receipts, custom metadata)
 * 2. Removing message actions
 * 3. Retrieving message actions with filtering and pagination
 * 4. Fetching messages with their associated actions
 * 5. Error handling and edge cases
 * 6. Practical use cases implementation
 */

echo "=== PubNub Message Actions Comprehensive Demo ===\n\n";

// Phase 1: Setup and Configuration
// snippet.setup
$publishKey = getenv('PUBLISH_KEY') ?: 'demo';
$subscribeKey = getenv('SUBSCRIBE_KEY') ?: 'demo';

$config = new PNConfiguration();
$config->setSubscribeKey($subscribeKey);
$config->setPublishKey($publishKey);
$config->setUserId("php-message-actions-demo-" . time());

$pubnub = new PubNub($config);
// snippet.end

// Sample data for testing
$testChannel = "message-actions-demo-" . time();
$publishedMessages = [];

echo "📝 Setting up test environment...\n";
echo "Channel: $testChannel\n";
echo "User ID: " . $config->getUserId() . "\n\n";

// Publish some test messages to work with
echo "📤 Publishing test messages...\n";
for ($i = 1; $i <= 3; $i++) {
    try {
        $result = $pubnub->publish()
            ->channel($testChannel)
            ->message([
                "id" => "msg_$i",
                "text" => "Test message #$i for Message Actions demo",
                "timestamp" => time(),
                "sender" => $config->getUserId()
            ])
            ->sync();

        $publishedMessages[] = [
            'id' => "msg_$i",
            'timetoken' => $result->getTimetoken()
        ];
        echo "✅ Published message #$i - Timetoken: {$result->getTimetoken()}\n";
    } catch (PubNubException $e) {
        echo "❌ Failed to publish message #$i: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Phase 2: Core Message Actions Features

// 2.1 Add Message Actions
echo "=== PHASE 2.1: Adding Message Actions ===\n\n";

$messageActions = [];

// Add emoji reactions
echo "😀 Adding emoji reactions...\n";
$emojiReactions = [
    ['type' => 'reaction', 'value' => '👍'],
    ['type' => 'reaction', 'value' => '❤️'],
    ['type' => 'reaction', 'value' => '😊'],
    ['type' => 'reaction', 'value' => '🎉']
];

foreach ($publishedMessages as $index => $msg) {
    if ($index < 2) { // Add reactions to first 2 messages
        $reaction = $emojiReactions[$index * 2];
        try {
            // snippet.add_emoji_reaction
            $messageAction = new PNMessageAction([
                'type' => $reaction['type'],
                'value' => $reaction['value'],
                'messageTimetoken' => $msg['timetoken']
            ]);

            $result = $pubnub->addMessageAction()
                ->channel($testChannel)
                ->messageAction($messageAction)
                ->sync();

            $messageActions[] = [
                'messageTimetoken' => $msg['timetoken'],
                'actionTimetoken' => $result->actionTimetoken,
                'type' => $reaction['type'],
                'value' => $reaction['value']
            ];

            echo "✅ Added {$reaction['value']} - Action Timetoken: {$result->actionTimetoken}\n";
            // snippet.end
        } catch (PubNubException $e) {
            echo "❌ Failed to add reaction to message {$msg['id']}: " . $e->getMessage() . "\n";
        }
    }
}

// Add read receipts
echo "\n📖 Adding read receipts...\n";
foreach ($publishedMessages as $index => $msg) {
    try {
        // snippet.add_read_receipt
        $result = $pubnub->addMessageAction()
            ->channel($testChannel)
            ->messageAction(new PNMessageAction([
                'type' => 'receipt',
                'value' => 'read',
                'messageTimetoken' => $msg['timetoken']
            ]))
            ->sync();

        $messageActions[] = [
            'messageTimetoken' => $msg['timetoken'],
            'actionTimetoken' => $result->actionTimetoken,
            'type' => 'receipt',
            'value' => 'read'
        ];

        echo "✅ Added read receipt to message {$msg['id']} - Action Timetoken: {$result->actionTimetoken}\n";
        // snippet.end
    } catch (PubNubException $e) {
        echo "❌ Failed to add read receipt to message {$msg['id']}: " . $e->getMessage() . "\n";
    }
}

// Add custom metadata
echo "\n🏷️ Adding custom metadata...\n";
$customActions = [
    ['type' => 'priority', 'value' => 'high'],
    ['type' => 'category', 'value' => 'announcement'],
    ['type' => 'flag', 'value' => 'important']
];

foreach ($publishedMessages as $index => $msg) {
    if ($index < count($customActions)) {
        $action = $customActions[$index];
        try {
            // snippet.add_custom_metadata
            $result = $pubnub->addMessageAction()
                ->channel($testChannel)
                ->messageAction(new PNMessageAction([
                    'type' => $action['type'],
                    'value' => $action['value'],
                    'messageTimetoken' => $msg['timetoken']
                ]))
                ->sync();

            $messageActions[] = [
                'messageTimetoken' => $msg['timetoken'],
                'actionTimetoken' => $result->actionTimetoken,
                'type' => $action['type'],
                'value' => $action['value']
            ];

            echo "✅ Added {$action['type']}:{$action['value']} - Action Timetoken: {$result->actionTimetoken}\n";
            // snippet.end
        } catch (PubNubException $e) {
            echo "❌ Failed to add custom metadata to message {$msg['id']}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n";

// 2.2 Retrieve Message Actions
echo "=== PHASE 2.2: Retrieving Message Actions ===\n\n";

// Get all message actions
echo "📋 Retrieving all message actions...\n";
try {
    // snippet.get_all_message_actions
    $result = $pubnub->getMessageActions()
        ->channel($testChannel)
        ->setLimit(100) // TODO: Add limit
        ->sync();

    echo "✅ Retrieved " . count($result->actions) . " message actions:\n";
    foreach ($result->actions as $action) {
        echo "  - {$action->type}:{$action->value} (A: {$action->actionTimetoken}, M: {$action->messageTimetoken})\n";
    }
    // snippet.end
} catch (PubNubException $e) {
    echo "❌ Failed to retrieve message actions: " . $e->getMessage() . "\n";
}

// Get message actions with time range
echo "\n📅 Retrieving message actions with time range...\n";
if (count($messageActions) >= 2) {
    try {
        // snippet.get_message_actions_with_range
        $result = $pubnub->getMessageActions()
            ->channel($testChannel)
            ->setStart($messageActions[count($messageActions) - 1]['actionTimetoken'])
            ->setEnd($messageActions[0]['actionTimetoken'])
            ->setLimit(50) // TODO: Add limit
            ->sync();

        echo "✅ Retrieved " . count($result->actions) . " message actions in range:\n";
        foreach ($result->actions as $action) {
            echo "  - {$action->type}:{$action->value} (Action TT: {$action->actionTimetoken})\n";
        }
        // snippet.end
    } catch (PubNubException $e) {
        echo "❌ Failed to retrieve message actions with range: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// 2.3 Fetch Messages with Actions
echo "=== PHASE 2.3: Fetching Messages with Actions ===\n\n";

echo "📬 Fetching messages with their associated actions...\n";
try {
    // snippet.fetch_messages_with_actions
    $result = $pubnub->fetchMessages()
        ->channels([$testChannel])
        ->includeMessageActions(true)
        ->includeMeta(true)
        ->includeUuid(true)
        ->sync();

    $messages = $result->getChannels()[$testChannel] ?? [];
    echo "✅ Retrieved " . count($messages) . " messages with actions:\n";

    foreach ($messages as $messageData) {
        echo "  📄 Message: " . json_encode($messageData->getMessage()) . "\n";
        echo "     Timetoken: {$messageData->getTimetoken()}\n";
        echo "     UUID: {$messageData->getUuid()}\n";

        $actions = $messageData->getActions();
        if (!empty($actions)) {
            echo "     Actions:\n";
            foreach ($actions as $actionType => $actionValues) {
                foreach ($actionValues as $actionValue => $actionDetails) {
                    foreach ($actionDetails as $actionDetail) {
                        echo "       - {$actionType}:{$actionValue} by {$actionDetail['uuid']} "
                        . "at {$actionDetail['actionTimetoken']}\n";
                    }
                }
            }
        }
        echo "\n";
    }
    // snippet.end
} catch (PubNubException $e) {
    echo "❌ Failed to fetch messages with actions: " . $e->getMessage() . "\n";
}

// 2.4 Remove Message Actions
echo "=== PHASE 2.4: Removing Message Actions ===\n\n";

echo "🗑️  Removing some message actions...\n";
$actionsToRemove = array_slice($messageActions, 0, 2); // Remove first 2 actions

foreach ($actionsToRemove as $actionToRemove) {
    try {
        // snippet.remove_message_action
        $result = $pubnub->removeMessageAction()
            ->channel($testChannel)
            ->messageTimetoken($actionToRemove['messageTimetoken'])
            ->actionTimetoken($actionToRemove['actionTimetoken'])
            ->sync();

        echo "✅ Removed {$actionToRemove['type']}:{$actionToRemove['value']} action "
            . "(A: {$actionToRemove['actionTimetoken']}, M: {$actionToRemove['messageTimetoken']})\n";
        // snippet.end
    } catch (PubNubException $e) {
        echo "❌ Failed to remove message action: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Phase 3: Error Handling and Edge Cases
echo "=== PHASE 3: Error Handling and Edge Cases ===\n\n";

echo "🔍 Testing error scenarios...\n";

// Try to add action to non-existent message
echo "📝 Testing action on non-existent message...\n";
try {
    // snippet.error_nonexistent_message
    $result = $pubnub->addMessageAction()
        ->channel($testChannel)
        ->messageAction(new PNMessageAction([
            'type' => 'test',
            'value' => 'error',
            'messageTimetoken' => "99999999999999999" // Non-existent timetoken
        ]))
        ->sync();

    echo "⚠️  Unexpectedly succeeded adding action to non-existent message\n";
    // snippet.end
} catch (PubNubException $e) {
    echo "✅ Expected error for non-existent message: " . $e->getMessage() . "\n";
}

// Try to remove non-existent action
echo "\n📝 Testing removal of non-existent action...\n";
try {
    // snippet.error_remove_nonexistent
    $result = $pubnub->removeMessageAction()
        ->channel($testChannel)
        ->messageTimetoken($publishedMessages[0]['timetoken'])
        ->actionTimetoken("99999999999999999") // Non-existent action timetoken
        ->sync();

    echo "✅ Successfully handled removal of non-existent action\n";
    // snippet.end
} catch (PubNubException $e) {
    echo "✅ Expected error for non-existent action: " . $e->getMessage() . "\n";
}

echo "\n";

// Phase 4: Practical Use Cases
echo "=== PHASE 4: Practical Use Cases ===\n\n";

// Use Case 1: Emoji Reactions System
echo "😊 Use Case 1: Emoji Reactions System\n";
function addEmojiReaction($pubnub, $channel, $messageTimetoken, $emoji, $userId)
{
    // snippet.emoji_reaction_system
    try {
        $result = $pubnub->addMessageAction()
            ->channel($channel)
            ->messageAction(new PNMessageAction([
                'type' => 'reaction',
                'value' => $emoji,
                'messageTimetoken' => $messageTimetoken
            ]))
            ->sync();

        return [
            'success' => true,
            'actionTimetoken' => $result->actionTimetoken,
            'message' => "User $userId reacted with $emoji"
        ];
    } catch (PubNubException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
    // snippet.end
}

$emojiResult = addEmojiReaction($pubnub, $testChannel, $publishedMessages[0]['timetoken'], '🚀', $config->getUserId());
echo "  " . ($emojiResult['success'] ? "✅" : "❌") . " " . ($emojiResult['message'] ?? $emojiResult['error']) . "\n";

// Use Case 2: Read Receipts System
echo "\n📖 Use Case 2: Read Receipts System\n";
function markAsRead($pubnub, $channel, $messageTimetoken, $userId)
{
    // snippet.read_receipt_system
    try {
        $result = $pubnub->addMessageAction()
            ->channel($channel)
            ->messageAction(new PNMessageAction([
                'type' => 'receipt',
                'value' => 'read',
                'messageTimetoken' => $messageTimetoken
            ]))
            ->sync();

        return [
            'success' => true,
            'actionTimetoken' => $result->actionTimetoken,
            'message' => "Message marked as read by $userId"
        ];
    } catch (PubNubException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
    // snippet.end
}

$readResult = markAsRead($pubnub, $testChannel, $publishedMessages[1]['timetoken'], $config->getUserId());
echo "  " . ($readResult['success'] ? "✅" : "❌") . " " . ($readResult['message'] ?? $readResult['error']) . "\n";

// Use Case 3: Message Delivery Confirmations
echo "\n📨 Use Case 3: Message Delivery Confirmations\n";
function confirmDelivery($pubnub, $channel, $messageTimetoken, $userId)
{
    // snippet.delivery_confirmation_system
    try {
        $result = $pubnub->addMessageAction()
            ->channel($channel)
            ->messageAction(new PNMessageAction([
                'type' => 'delivery',
                'value' => 'confirmed',
                'messageTimetoken' => $messageTimetoken
            ]))
            ->sync();

        return [
            'success' => true,
            'actionTimetoken' => $result->actionTimetoken,
            'message' => "Delivery confirmed by $userId"
        ];
    } catch (PubNubException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
    // snippet.end
}

$deliveryResult = confirmDelivery($pubnub, $testChannel, $publishedMessages[2]['timetoken'], $config->getUserId());
echo "  " . ($deliveryResult['success'] ? "✅" : "❌") . " "
    . ($deliveryResult['message'] ?? $deliveryResult['error']) . "\n";

// Use Case 4: Message Analytics Tagging
echo "\n📊 Use Case 4: Message Analytics Tagging\n";
function tagForAnalytics($pubnub, $channel, $messageTimetoken, $tag, $value)
{
    // snippet.analytics_tagging_system
    try {
        $result = $pubnub->addMessageAction()
            ->channel($channel)
            ->messageAction(new PNMessageAction([
                'type' => $tag,
                'value' => $value,
                'messageTimetoken' => $messageTimetoken
            ]))
            ->sync();

        return [
            'success' => true,
            'actionTimetoken' => $result->actionTimetoken,
            'message' => "Tagged message with $tag:$value"
        ];
    } catch (PubNubException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
    // snippet.end
}

$tagResult = tagForAnalytics($pubnub, $testChannel, $publishedMessages[0]['timetoken'], 'engagement', 'high');
echo "  " . ($tagResult['success'] ? "✅" : "❌") . " " . ($tagResult['message'] ?? $tagResult['error']) . "\n";

echo "\n";

// Final verification
echo "=== FINAL VERIFICATION ===\n\n";

echo "🔍 Final verification - getting all remaining message actions...\n";
try {
    $finalResult = $pubnub->getMessageActions()
        ->channel($testChannel)
        ->setLimit(100)
        ->sync();

    echo "✅ Final count: " . count($finalResult->actions) . " message actions remaining\n";

    // Group actions by type for summary
    $actionsByType = [];
    foreach ($finalResult->actions as $action) {
        $type = $action->type;
        if (!isset($actionsByType[$type])) {
            $actionsByType[$type] = 0;
        }
        $actionsByType[$type]++;
    }

    echo "📊 Summary by type:\n";
    foreach ($actionsByType as $type => $count) {
        echo "  - $type: $count actions\n";
    }
} catch (PubNubException $e) {
    echo "❌ Failed to get final verification: " . $e->getMessage() . "\n";
}

echo "\n=== MESSAGE ACTIONS DEMO COMPLETE ===\n";
