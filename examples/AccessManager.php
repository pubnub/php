<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\PubNub;
use PubNub\PNConfiguration;
use PubNub\Exceptions\PubNubServerException;

// Get keys from environment variables with defaults
$publishKey = getenv('PUBLISH_PAM_KEY') ?? 'demo';
$subscribeKey = getenv('SUBSCRIBE_PAM_KEY') ?? 'demo';
$secretKey = getenv('SECRET_PAM_KEY') ?? 'demo';

// Admin instance with full access (includes secret key)
$adminConfig = new PNConfiguration();
$adminConfig->setPublishKey($publishKey);
$adminConfig->setSubscribeKey($subscribeKey);
$adminConfig->setSecretKey($secretKey);
$adminConfig->setUuid('admin-user');

$admin = new PubNub($adminConfig);

// Regular user instance (without secret key)
$userConfig = new PNConfiguration();
$userConfig->setPublishKey($publishKey);
$userConfig->setSubscribeKey($subscribeKey);
$userConfig->setUuid('regular-user');
$user = new PubNub($userConfig);

// Helper function to print test results
function printResult($testName, $success, $message = '')
{
    $status = $success ? "✅ PASS" : "❌ FAIL";
    echo "$status - $testName";
    if ($message) {
        echo ": $message";
    }
    echo "\n";
}

// Helper function to safely execute operations (deprecated - using direct try/catch now)
function safeTest($callable, $description)
{
    try {
        $result = $callable();
        printResult($description, true, "Success");
        return $result;
    } catch (PubNubServerException $e) {
        printResult($description, false, "Server Error: " . $e->getMessage());
        return null;
    } catch (Exception $e) {
        printResult($description, false, "Error: " . $e->getMessage());
        return null;
    }
}

echo "🔧 Step 0: Prepare channels and users metadata\n";
echo "----------------------------------------------\n";

// snippet.prepare_metadata
// Create channel metadata for demo channels
$channels = ['public-channel', 'read-only-channel', 'private-channel', 'admin-only-channel'];
foreach ($channels as $channel) {
    try {
        $admin->setChannelMetadata()
            ->channel($channel)
            ->setName(ucwords(str_replace('-', ' ', $channel)))
            ->setDescription("Demo channel for access manager testing - " . $channel)
            ->setCustom([
                'type' => $channel === 'admin-only-channel' ? 'admin' : 'user',
                'created' => date('Y-m-d H:i:s'),
                'demo' => true
            ])
            ->sync();
        printResult("Create metadata for channel: $channel", true);
    } catch (Exception $e) {
        printResult("Create metadata for channel: $channel", false, $e->getMessage());
    }
}

echo "\n";

// Create user metadata for demo users
$users = [
    'admin-user' => [
        'name' => 'Administrator User',
        'email' => 'admin@example.com',
        'role' => 'admin'
    ],
    'regular-user' => [
        'name' => 'Regular User',
        'email' => 'user@example.com',
        'role' => 'user'
    ],
    'other-user' => [
        'name' => 'Other User',
        'email' => 'other@example.com',
        'role' => 'user'
    ]
];

foreach ($users as $uuid => $userData) {
    try {
        $admin->setUuidMetadata()
            ->uuid($uuid)
            ->name($userData['name'])
            ->email($userData['email'])
            ->custom([
                'role' => $userData['role'],
                'created' => date('Y-m-d H:i:s'),
                'demo' => true,
                'preferences' => [
                    'notifications' => true,
                    'theme' => 'light'
                ]
            ])
            ->sync();
        printResult("Create metadata for user: $uuid", true);
    } catch (Exception $e) {
        printResult("Create metadata for user: $uuid", false, $e->getMessage());
    }
}

echo "\nMetadata preparation complete!\n\n";
// snippet.end

// Step 1: Admin grants a comprehensive token
echo "📝 Step 1: Admin grants token with different permission levels\n";
echo "-------------------------------------------------------------\n";

// snippet.grant_token
try {
    $token = $admin->grantToken()
        ->ttl(60) // 60 minutes
        ->authorizedUuid('regular-user') // Restrict to specific user
        ->addChannelResources([
            'public-channel' => ['read' => true, 'write' => true], // Full access
            'read-only-channel' => ['read' => true], // Read only - no write
            'private-channel' => ['read' => true, 'write' => true, 'manage' => true] // Full access including manage
        ])
        ->addChannelGroupResources([
            'user-group' => ['read' => true] // Read only for channel groups
        ])
        ->addUuidResources([
            'regular-user' => ['get' => true, 'update' => true], // Self metadata access
            'other-user' => ['get' => true] // Read-only access to other user's metadata
        ])
        ->meta([
            'purpose' => 'demo-token',
            'issued-by' => 'admin-user'
        ])
        ->sync();

    printResult("Grant comprehensive token", true);
    echo "Generated token: " . substr($token, 0, 50) . "...\n\n";
} catch (Exception $e) {
    printResult("Grant comprehensive token", false, $e->getMessage());
    echo "⚠️  Cannot continue without token. Exiting.\n";
    exit(1);
}
// snippet.end

// Step 2: Parse the token to show its contents
echo "🔍 Step 2: Parse token to examine embedded permissions\n";
echo "----------------------------------------------------\n";

// snippet.parse_token
try {
    $parsedToken = $admin->parseToken($token);
    printResult("Parse token", true);

    echo "Token Details:\n";
    echo "- Version: " . $parsedToken->getVersion() . "\n";
    echo "- TTL: " . $parsedToken->getTtl() . " minutes\n";
    echo "- Authorized UUID: " . ($parsedToken->getUuid() ?? 'None') . "\n";
    echo "- Timestamp: " . date('Y-m-d H:i:s', $parsedToken->getTimestamp()) . "\n";

    // Show channel permissions
    echo "\nChannel Permissions:\n";
    foreach (['public-channel', 'read-only-channel', 'private-channel'] as $channel) {
        $permissions = $parsedToken->getChannelResource($channel);
        if ($permissions) {
            echo "- $channel: ";
            $perms = [];
            if ($permissions->hasRead()) {
                $perms[] = 'read';
            }
            if ($permissions->hasWrite()) {
                $perms[] = 'write';
            }
            if ($permissions->hasManage()) {
                $perms[] = 'manage';
            }
            echo implode(', ', $perms) ?: 'none';
            echo "\n";
        }
    }

    // Show metadata
    $metadata = $parsedToken->getMetadata();
    if ($metadata) {
        echo "\nToken Metadata:\n";
        foreach ($metadata as $key => $value) {
            echo "- $key: $value\n";
        }
    }
} catch (Exception $e) {
    printResult("Parse token", false, $e->getMessage());
}
// snippet.end

echo "\n";

// Step 3: Test user access WITHOUT token (should fail)
echo "🚫 Step 3: Test user access WITHOUT token\n";
echo "----------------------------------------\n";

// snippet.access_denied_without_token
try {
    $user->publish()
        ->channel('public-channel')
        ->message(['text' => 'Hello without token!'])
        ->sync();
    printResult("User publish to public-channel WITHOUT token", false, "Should have failed but succeeded");
} catch (Exception $e) {
    printResult("User publish to public-channel WITHOUT token", true, "Correctly denied access");
}

try {
    $user->history()
        ->channel('public-channel')
        ->count(1)
        ->sync();
    printResult("User read from public-channel WITHOUT token", false, "Should have failed but succeeded");
} catch (Exception $e) {
    printResult("User read from public-channel WITHOUT token", true, "Correctly denied access");
}
// snippet.end

echo "\n";

// Step 4: Set token and test access
echo "🔑 Step 4: Set token and test permitted operations\n";
echo "-------------------------------------------------\n";

// snippet.access_granted_with_token
// Set the token for the user
$user->setToken($token);
echo "Token set for user.\n";

// Test allowed operations
try {
    $result = $user->publish()
        ->channel('public-channel')
        ->message(['text' => 'Hello with token!', 'timestamp' => time()])
        ->sync();
    printResult("User publish to public-channel WITH token", true, "Message published successfully");
} catch (Exception $e) {
    printResult("User publish to public-channel WITH token", false, $e->getMessage());
}

try {
    $result = $user->history()
        ->channel('public-channel')
        ->count(5)
        ->sync();
    printResult("User read from public-channel WITH token", true, "History retrieved successfully");
} catch (Exception $e) {
    printResult("User read from public-channel WITH token", false, $e->getMessage());
}

try {
    $result = $user->history()
        ->channel('private-channel')
        ->count(5)
        ->sync();
    printResult("User read from private-channel WITH token", true, "History retrieved successfully");
} catch (Exception $e) {
    printResult("User read from private-channel WITH token", false, $e->getMessage());
}
// snippet.end

echo "\n";

// Step 5: Test restricted operations (should fail)
echo "🚫 Step 5: Test operations beyond token permissions\n";
echo "--------------------------------------------------\n";

// snippet.permission_enforcement
// Test read-only channel (can read but not write)
try {
    $user->history()
        ->channel('read-only-channel')
        ->count(1)
        ->sync();
    printResult("User read from read-only-channel WITH token", true, "Read access granted");
} catch (Exception $e) {
    printResult("User read from read-only-channel WITH token", false, $e->getMessage());
}

try {
    $user->publish()
        ->channel('read-only-channel')
        ->message(['text' => 'Trying to write to read-only channel'])
        ->sync();
    printResult("User publish to read-only-channel WITH token", false, "Should have failed but succeeded");
} catch (Exception $e) {
    printResult("User publish to read-only-channel WITH token", true, "Correctly denied write access");
}

// Test channel not in token (should fail)
try {
    $user->publish()
        ->channel('admin-only-channel')
        ->message(['text' => 'Trying to access admin channel'])
        ->sync();
    printResult("User publish to admin-only-channel WITH token", false, "Should have failed but succeeded");
} catch (Exception $e) {
    printResult("User publish to admin-only-channel WITH token", true, "Correctly denied - no permissions");
}

try {
    $user->history()
        ->channel('admin-only-channel')
        ->count(1)
        ->sync();
    printResult("User read from admin-only-channel WITH token", false, "Should have failed but succeeded");
} catch (Exception $e) {
    printResult("User read from admin-only-channel WITH token", true, "Correctly denied - no permissions");
}
// snippet.end

echo "\n";

// Step 6: Test UUID metadata operations
echo "👤 Step 6: Test UUID metadata operations\n";
echo "---------------------------------------\n";

// snippet.uuid_metadata_operations
try {
    $result = $user->setUUIDMetadata()
        ->uuid('regular-user')
        ->name('Regular User')
        ->email('user@example.com')
        ->sync();
    printResult("User update own metadata WITH token", true, "Metadata updated successfully");
} catch (Exception $e) {
    printResult("User update own metadata WITH token", false, $e->getMessage());
}

try {
    $result = $user->getUUIDMetadata()
        ->uuid('regular-user')
        ->sync();
    printResult("User get own metadata WITH token", true, "Metadata retrieved successfully");
} catch (Exception $e) {
    printResult("User get own metadata WITH token", false, $e->getMessage());
}

try {
    $result = $user->getUUIDMetadata()
        ->uuid('other-user')
        ->sync();
    printResult("User get other user metadata WITH token", true, "Read-only access allowed");
} catch (Exception $e) {
    printResult("User get other user metadata WITH token", false, $e->getMessage());
}

try {
    $user->setUUIDMetadata()
        ->uuid('other-user')
        ->name('Cannot update this')
        ->sync();
    printResult("User update other user metadata WITH token", false, "Should have failed but succeeded");
} catch (Exception $e) {
    printResult("User update other user metadata WITH token", true, "Correctly denied - read only");
}
// snippet.end

echo "\n";

// Step 7: Admin operations (always work with secret key)
echo "👑 Step 7: Admin operations (unrestricted access)\n";
echo "------------------------------------------------\n";

// snippet.admin_unrestricted_access
try {
    $result = $admin->publish()
        ->channel('admin-only-channel')
        ->message(['text' => 'Admin message', 'timestamp' => time()])
        ->sync();
    printResult("Admin publish to admin-only-channel", true, "Admin has unrestricted access");
} catch (Exception $e) {
    printResult("Admin publish to admin-only-channel", false, $e->getMessage());
}

try {
    $result = $admin->history()
        ->channel('admin-only-channel')
        ->count(5)
        ->sync();
    printResult("Admin read from admin-only-channel", true, "Admin has unrestricted access");
} catch (Exception $e) {
    printResult("Admin read from admin-only-channel", false, $e->getMessage());
}
// snippet.end

echo "\n";

// Step 8: Revoke the token
echo "🗑️  Step 8: Revoke token and test access\n";
echo "--------------------------------------\n";

// snippet.revoke_token
try {
    $admin->setToken($token);
    $admin->revokeToken()->token($token)->sync();
    $admin->setToken('');
    printResult("Admin revoke token", true);
    // token revoke propagation might take some time
    sleep(15);
    // Test user access after revocation (should fail)
    try {
        $user->publish()
            ->channel('public-channel')
            ->message(['text' => 'Hello after revocation!'])
            ->sync();
        printResult("User publish after token revocation", false, "Should have failed but succeeded");
    } catch (Exception $e) {
        printResult("User publish after token revocation", true, "Correctly denied - token revoked");
    }
} catch (Exception $e) {
    printResult("Admin revoke token", false, $e->getMessage());
}
// snippet.end

echo "\n";

// Step 9: Pattern-based permissions example
echo "🎯 Step 9: Pattern-based permissions example\n";
echo "-------------------------------------------\n";

// snippet.pattern_permissions
try {
    $patternToken = $admin->grantToken()
        ->ttl(30)
        ->authorizedUuid('regular-user')
        ->addChannelPatterns([
            'news.*' => ['read' => true], // Read access to all channels starting with 'news'
            'chat.*' => ['read' => true, 'write' => true] // Full access to all 'chat' channels
        ])
        ->addUuidPatterns([
            'user.*' => ['get' => true] // Read access to all UUIDs starting with 'user'
        ])
        ->sync();

    printResult("Grant pattern-based token", true);
    echo "Pattern token: " . substr($patternToken, 0, 50) . "...\n";

    // Parse pattern token
    try {
        $parsedPatternToken = $admin->parseToken($patternToken);
        printResult("Parse pattern token", true);

        echo "\nPattern Permissions:\n";
        $patterns = $parsedPatternToken->getPatterns();
        if (isset($patterns['chan'])) {
            foreach ($patterns['chan'] as $pattern => $perms) {
                $grantedPerms = [];
                if (is_array($perms)) {
                    $grantedPerms = array_keys(array_filter($perms));
                }
                echo "- Channel pattern '$pattern': " . implode(', ', $grantedPerms) . "\n";
            }
        }
        if (isset($patterns['uuid'])) {
            foreach ($patterns['uuid'] as $pattern => $perms) {
                $grantedPerms = [];
                if (is_array($perms)) {
                    $grantedPerms = array_keys(array_filter($perms));
                }
                echo "- UUID pattern '$pattern': " . implode(', ', $grantedPerms) . "\n";
            }
        }
    } catch (Exception $e) {
        printResult("Parse pattern token", false, $e->getMessage());
    }

    // Test pattern-based access
    $user->setToken($patternToken);

    try {
        $result = $user->history()
            ->channel('news-sports')
            ->count(1)
            ->sync();
        printResult("User read 'news-sports' with pattern token", true, "Pattern matched - access granted");
    } catch (Exception $e) {
        printResult("User read 'news-sports' with pattern token", false, $e->getMessage());
    }

    try {
        $result = $user->publish()
            ->channel('chat-general')
            ->message(['text' => 'Hello chat!'])
            ->sync();
        printResult("User publish to 'chat-general' with pattern token", true, "Pattern matched - access granted");
    } catch (Exception $e) {
        printResult("User publish to 'chat-general' with pattern token", false, $e->getMessage());
    }

    try {
        $user->publish()
            ->channel('news-politics')
            ->message(['text' => 'Cannot write here'])
            ->sync();
        printResult("User publish to 'news-politics' with pattern token", false, "Should have failed but succeeded");
    } catch (Exception $e) {
        printResult("User publish to 'news-politics' with pattern token", true, "Correctly denied - read only");
    }
} catch (Exception $e) {
    printResult("Grant pattern-based token", false, $e->getMessage());
}
// snippet.end

echo "\n🎉 Access Manager Demo Complete!\n";
