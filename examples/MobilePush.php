<?php

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
namespace PubNub\Examples;

// Include Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Enums\PNPushType;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubException;
use PubNub\Exceptions\PubNubValidationException;
use Exception;

// Demo configuration and sample data
class MobilePushDemo
{
    private $pubnub;
    private $sampleChannels;
    private $sampleDevices;

    public function __construct()
    {
        $this->initializePubNub();
        $this->initializeSampleData();
    }

    private function initializePubNub()
    {
        // snippet.setup
        echo "🚀 Initializing PubNub Mobile Push Demo...\n";
        echo "================================================\n\n";

        $pnConfig = new PNConfiguration();
        $pnConfig->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?: 'demo');
        $pnConfig->setPublishKey(getenv('PUBLISH_KEY') ?: 'demo');
        $pnConfig->setUserId('php-mobile-push-demo-' . time());

        $this->pubnub = new PubNub($pnConfig);

        echo "✅ PubNub configured with demo keys\n";
        echo "📱 User ID: " . $pnConfig->getUserId() . "\n\n";
        // snippet.end
    }

    private function initializeSampleData()
    {
        // snippet.sample_data
        $this->sampleChannels = [
            'news-channel-' . time(),
            'alerts-channel-' . time(),
            'promotions-channel-' . time(),
            'updates-channel-' . time()
        ];

        $this->sampleDevices = [
            'fcm' => [
                'deviceId' => 'fcm-device-token-' . uniqid(),
                'pushType' => PNPushType::FCM,
                'name' => 'Android Device (FCM)'
            ],
            'apns2_dev' => [
                'deviceId' => 'apns2-dev-token-' . uniqid(),
                'pushType' => PNPushType::APNS2,
                'environment' => 'development',
                'topic' => 'com.example.myapp',
                'name' => 'iOS Device (APNS2 - Development)'
            ],
            'apns2_prod' => [
                'deviceId' => 'apns2-prod-token-' . uniqid(),
                'pushType' => PNPushType::APNS2,
                'environment' => 'production',
                'topic' => 'com.example.myapp',
                'name' => 'iOS Device (APNS2 - Production)'
            ]
        ];
        // snippet.end

        echo "📋 Sample data initialized:\n";
        echo "   Channels: " . implode(', ', $this->sampleChannels) . "\n";
        echo "   Devices: " . count($this->sampleDevices) . " sample devices\n\n";
    }

    public function runFullDemo()
    {
        echo "🎯 Starting comprehensive Mobile Push demo...\n";
        echo "==============================================\n\n";

        // Test all features for each device type
        foreach ($this->sampleDevices as $deviceKey => $device) {
            $this->runDeviceDemo($deviceKey, $device);
            echo "\n" . str_repeat("-", 60) . "\n\n";
        }

        echo "✨ Mobile Push demo completed successfully!\n";
    }

    private function runDeviceDemo($deviceKey, $device)
    {
        echo "📱 Testing {$device['name']}\n";
        echo "   Device ID: {$device['deviceId']}\n";
        echo "   Push Type: {$device['pushType']}\n";

        if (isset($device['environment'])) {
            echo "   Environment: {$device['environment']}\n";
        }
        if (isset($device['topic'])) {
            echo "   Topic: {$device['topic']}\n";
        }
        echo "\n";

        // 1. Add device to channels
        $this->demonstrateAddDeviceToChannels($device);

        // 2. List channels for device
        $this->demonstrateListChannelsForDevice($device);

        // 3. Remove device from some channels
        $this->demonstrateRemoveDeviceFromChannels($device);

        // 4. List channels again to verify removal
        $this->demonstrateListChannelsForDevice($device, "After Removal");

        // 5. Remove all channels from device
        $this->demonstrateRemoveAllChannelsFromDevice($device);

        // 6. Final verification
        $this->demonstrateListChannelsForDevice($device, "Final Verification");
    }

    private function demonstrateAddDeviceToChannels($device)
    {
        echo "1️⃣ Adding device to channels...\n";

        try {
            if ($device['pushType'] === PNPushType::FCM) {
                // snippet.add_device_to_channel_fcm
                $result = $this->pubnub->addChannelsToPush()
                    ->pushType(PNPushType::FCM)
                    ->channels($this->sampleChannels)
                    ->deviceId($device['deviceId'])
                    ->sync();
                // snippet.end
            } elseif ($device['pushType'] === PNPushType::APNS2) {
                // snippet.add_device_to_channel_apns2
                $result = $this->pubnub->addChannelsToPush()
                    ->pushType(PNPushType::APNS2)
                    ->channels($this->sampleChannels)
                    ->deviceId($device['deviceId'])
                    ->environment($device['environment'])
                    ->topic($device['topic'])
                    ->sync();
                // snippet.end
            }

            echo "   ✅ Successfully added device to " . count($this->sampleChannels) . " channels\n";
            echo "   📋 Channels: " . implode(', ', $this->sampleChannels) . "\n\n";
        } catch (PubNubServerException $e) {
            echo "   ❌ Server Error: " . $e->getMessage() . "\n";
            echo "   🔍 Status Code: " . $e->getStatusCode() . "\n\n";
        } catch (PubNubException $e) {
            echo "   ❌ PubNub Error: " . $e->getMessage() . "\n\n";
        } catch (Exception $e) {
            echo "   ❌ General Error: " . $e->getMessage() . "\n\n";
        }
    }

    private function demonstrateListChannelsForDevice($device, $context = "")
    {
        $title = $context ? "2️⃣ Listing channels for device ($context)..." : "2️⃣ Listing channels for device...";
        echo "$title\n";

        try {
            if ($device['pushType'] === PNPushType::FCM) {
                // snippet.list_channels_fcm
                $result = $this->pubnub->listPushProvisions()
                    ->pushType(PNPushType::FCM)
                    ->deviceId($device['deviceId'])
                    ->sync();
                // snippet.end
            } elseif ($device['pushType'] === PNPushType::APNS2) {
                // snippet.list_channels_apns2
                $result = $this->pubnub->listPushProvisions()
                    ->pushType(PNPushType::APNS2)
                    ->deviceId($device['deviceId'])
                    ->environment($device['environment'])
                    ->topic($device['topic'])
                    ->sync();
                // snippet.end
            }

            $channels = $result->getChannels();

            if (!empty($channels)) {
                echo "   ✅ Device is registered for " . count($channels) . " channels:\n";
                foreach ($channels as $channel) {
                    echo "      • $channel\n";
                }
            } else {
                echo "   📝 No channels found for this device\n";
            }
            echo "\n";
        } catch (PubNubServerException $e) {
            echo "   ❌ Server Error: " . $e->getMessage() . "\n";
            echo "   🔍 Status Code: " . $e->getStatusCode() . "\n\n";
        } catch (PubNubException $e) {
            echo "   ❌ PubNub Error: " . $e->getMessage() . "\n\n";
        } catch (Exception $e) {
            echo "   ❌ General Error: " . $e->getMessage() . "\n\n";
        }
    }

    private function demonstrateRemoveDeviceFromChannels($device)
    {
        echo "3️⃣ Removing device from specific channels...\n";

        // Remove from first 2 channels only
        $channelsToRemove = array_slice($this->sampleChannels, 0, 2);

        try {
            if ($device['pushType'] === PNPushType::FCM) {
                // snippet.remove_device_from_channels_fcm
                $result = $this->pubnub->removeChannelsFromPush()
                    ->pushType(PNPushType::FCM)
                    ->channels($channelsToRemove)
                    ->deviceId($device['deviceId'])
                    ->sync();
                    // snippet.end
            } elseif ($device['pushType'] === PNPushType::APNS2) {
                // snippet.remove_device_from_channels_apns2
                $result = $this->pubnub->removeChannelsFromPush()
                    ->pushType(PNPushType::APNS2)
                    ->channels($channelsToRemove)
                    ->deviceId($device['deviceId'])
                    ->environment($device['environment'])
                    ->topic($device['topic'])
                    ->sync();
                // snippet.end
            }

            echo "   ✅ Successfully removed device from " . count($channelsToRemove) . " channels\n";
            echo "   📋 Removed from: " . implode(', ', $channelsToRemove) . "\n\n";
        } catch (PubNubServerException $e) {
            echo "   ❌ Server Error: " . $e->getMessage() . "\n";
            echo "   🔍 Status Code: " . $e->getStatusCode() . "\n\n";
        } catch (PubNubException $e) {
            echo "   ❌ PubNub Error: " . $e->getMessage() . "\n\n";
        } catch (Exception $e) {
            echo "   ❌ General Error: " . $e->getMessage() . "\n\n";
        }
    }

    private function demonstrateRemoveAllChannelsFromDevice($device)
    {
        echo "4️⃣ Removing all push channels from device...\n";

        try {
            if ($device['pushType'] === PNPushType::FCM) {
                // snippet.remove_all_channels_fcm
                $result = $this->pubnub->removeAllPushChannelsForDevice()
                    ->pushType(PNPushType::FCM)
                    ->deviceId($device['deviceId'])
                    ->sync();
                // snippet.end
            } elseif ($device['pushType'] === PNPushType::APNS2) {
                // snippet.remove_all_channels_apns2
                $result = $this->pubnub->removeAllPushChannelsForDevice()
                    ->pushType(PNPushType::APNS2)
                    ->deviceId($device['deviceId'])
                    ->sync();
                // snippet.end

                // snippet.remove_all_channels_response_check
                $response = $this->pubnub->removeAllPushChannelsForDevice()
                    ->pushType(PNPushType::APNS2)
                    ->deviceId("yourDeviceId")
                    ->sync();

                if ($response->isSuccessful()) {
                    echo "Successfully removed all push channels from device.";
                } else {
                    echo "Failed to remove push channels.";
                }
                // snippet.end
            }

            echo "   ✅ Successfully removed all push channels from device\n\n";
        } catch (PubNubServerException $e) {
            echo "   ❌ Server Error: " . $e->getMessage() . "\n";
            echo "   🔍 Status Code: " . $e->getStatusCode() . "\n\n";
        } catch (PubNubException $e) {
            echo "   ❌ PubNub Error: " . $e->getMessage() . "\n\n";
        } catch (Exception $e) {
            echo "   ❌ General Error: " . $e->getMessage() . "\n\n";
        }
    }

    public function demonstrateAdvancedFeatures()
    {
        echo "🔧 Advanced Mobile Push Features Demo\n";
        echo "====================================\n\n";

        // Demonstrate error handling scenarios
        $this->demonstrateErrorHandling();

        // Demonstrate bulk operations
        $this->demonstrateBulkOperations();
    }

    private function demonstrateErrorHandling()
    {
        echo "🛠️ Error Handling Scenarios:\n\n";

        // Test with invalid push type
        echo "📋 Testing invalid device ID...\n";
        try {
            $result = $this->pubnub->addChannelsToPush()
                ->pushType(PNPushType::FCM)
                ->channels(['test-channel'])
                ->deviceId('') // Empty device ID
                ->sync();
        } catch (PubNubValidationException $e) {
            echo "   ✅ Correctly caught error: " . $e->getMessage() . "\n\n";
        }

        // Test with invalid channels
        echo "📋 Testing with empty channels array...\n";
        try {
            $result = $this->pubnub->addChannelsToPush()
                ->pushType(PNPushType::FCM)
                ->channels([]) // Empty channels
                ->deviceId('test-device-id')
                ->sync();
        } catch (PubNubValidationException $e) {
            echo "   ✅ Correctly caught error: " . $e->getMessage() . "\n\n";
        }
    }

    private function demonstrateBulkOperations()
    {
        echo "📦 Bulk Operations Demo:\n\n";

        $bulkChannels = [
            'bulk-news-' . time(),
            'bulk-sports-' . time(),
            'bulk-weather-' . time(),
            'bulk-traffic-' . time(),
            'bulk-entertainment-' . time()
        ];

        $bulkDevice = [
            'deviceId' => 'bulk-demo-device-' . uniqid(),
            'pushType' => PNPushType::FCM,
            'name' => 'Bulk Demo Device'
        ];

        echo "📱 Adding device to " . count($bulkChannels) . " channels at once...\n";

        try {
            $result = $this->pubnub->addChannelsToPush()
                ->pushType($bulkDevice['pushType'])
                ->channels($bulkChannels)
                ->deviceId($bulkDevice['deviceId'])
                ->sync();

            echo "   ✅ Successfully added to all channels\n";

            // Verify the addition
            $listResult = $this->pubnub->listPushProvisions()
                ->pushType($bulkDevice['pushType'])
                ->deviceId($bulkDevice['deviceId'])
                ->sync();

            $registeredChannels = $listResult->getChannels();
            echo "   📋 Verified: Device registered for " . count($registeredChannels) . " channels\n";

            // Clean up - remove all
            $this->pubnub->removeAllPushChannelsForDevice()
                ->pushType($bulkDevice['pushType'])
                ->deviceId($bulkDevice['deviceId'])
                ->sync();

            echo "   🧹 Cleaned up: Removed all channels\n\n";
        } catch (Exception $e) {
            echo "   ❌ Error in bulk operations: " . $e->getMessage() . "\n\n";
        }
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    try {
        $demo = new MobilePushDemo();

        // Run the comprehensive demo
        $demo->runFullDemo();

        // Run advanced features demo
        $demo->demonstrateAdvancedFeatures();

        echo "🎉 All Mobile Push demos completed successfully!\n";
        echo "📚 Refer to PubNub docs: https://www.pubnub.com/docs/sdks/php/api-reference/mobile-push\n";
    } catch (Exception $e) {
        echo "💥 Fatal Error: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        exit(1);
    }
} else {
    echo "⚠️  This demo is designed to run from the command line.\n";
    echo "Usage: php " . basename(__FILE__) . "\n";
}
