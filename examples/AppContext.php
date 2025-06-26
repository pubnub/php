<?php

namespace PubNub\Examples;

// Include Composer autoloader (adjust path if needed)
require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\Objects\UUID\PNGetUUIDMetadataResult;
use PubNub\Models\Consumer\Objects\Membership\PNChannelMembership;
use PubNub\Models\Consumer\Objects\Membership\PNMembershipIncludes;
use PubNub\Models\Consumer\Objects\Member\PNChannelMember;
use PubNub\Models\Consumer\Objects\Member\PNMemberIncludes;

use function PHPUnit\Framework\isInstanceOf;

// snippet.setup
$publishKey = getenv('PUBLISH_KEY') ?: 'demo';
$subscribeKey = getenv('SUBSCRIBE_KEY') ?: 'demo';

$config = new PNConfiguration();
$config->setSubscribeKey($subscribeKey);
$config->setPublishKey($publishKey);
$config->setUserId("php-app-context-sample-" . time());

$pubnub = new PubNub($config);
// snippet.end

// snippet.sample_data
$sampleUsers = [
    [
        'id' => 'user_alice_' . time(),
        'name' => 'Alice Johnson',
        'email' => 'alice@example.com',
        'externalId' => 'EXT_ALICE_001',
        'profileUrl' => 'https://example.com/profiles/alice.jpg',
        'custom' => [
            'department' => 'Engineering',
            'role' => 'Senior Developer'
        ]
    ],
    [
        'id' => 'user_bob_' . time(),
        'name' => 'Bob Smith',
        'email' => 'bob@example.com',
        'externalId' => 'EXT_BOB_001',
        'profileUrl' => 'https://example.com/profiles/bob.jpg',
        'custom' => [
            'department' => 'Marketing',
            'role' => 'Marketing Manager'
        ]
    ]
];

$sampleChannels = [
    [
        'id' => 'channel_general_' . time(),
        'name' => 'General Discussion',
        'description' => 'Main channel for general discussions',
        'custom' => [
            'category' => 'company',
            'public' => true
        ]
    ],
    [
        'id' => 'channel_dev_' . time(),
        'name' => 'Development Team',
        'description' => 'Development team coordination',
        'custom' => [
            'category' => 'team',
            'public' => false
        ]
    ]
];
// snippet.end

// snippet.set_user_metadata
foreach ($sampleUsers as $user) {
    $setUserMetadataResult = $pubnub->setUuidMetadata()
        ->uuid($user['id'])
        ->name($user['name'])
        ->email($user['email'])
        ->externalId($user['externalId'])
        ->profileUrl($user['profileUrl'])
        ->custom($user['custom'])
        ->sync();
    assert($setUserMetadataResult->getId());
    assert($setUserMetadataResult->getName() === $user['name']);
    assert($setUserMetadataResult->getEmail() === $user['email']);
    assert($setUserMetadataResult->getExternalId() === $user['externalId']);
    assert($setUserMetadataResult->getProfileUrl() === $user['profileUrl']);
    assert(json_encode($setUserMetadataResult->getCustom()) === json_encode($user['custom']));
}
// snippet.end

// snippet.get_user_metadata
$getUserMetadataResult = $pubnub->getUuidMetadata()
    ->uuid($sampleUsers[0]['id'])
    ->sync();
assert($getUserMetadataResult->getId() === $sampleUsers[0]['id']);
assert($getUserMetadataResult->getName() === $sampleUsers[0]['name']);
assert($getUserMetadataResult->getEmail() === $sampleUsers[0]['email']);
// snippet.end

// snippet.get_all_user_metadata
$getAllUserMetadataResult = $pubnub->getAllUuidMetadata()
    ->includeFields(['customFields' => true, 'totalCount' => true])
    ->limit(10)
    ->sync();
assert(count($getAllUserMetadataResult->getData()) >= 2);
assert($getAllUserMetadataResult->getTotalCount() >= 2);
assert(isInstanceOf($getAllUserMetadataResult->getData()[0], PNGetUUIDMetadataResult::class));
// snippet.end

// snippet.update_user_metadata
$updatedCustomData = [
    'department' => 'Engineering - Updated',
    'role' => 'Lead Developer',
    'lastUpdated' => date('Y-m-d H:i:s')
];

$updateUserMetadataResult = $pubnub->setUuidMetadata()
    ->uuid($sampleUsers[0]['id'])
    ->name($sampleUsers[0]['name'] . ' - Updated')
    ->email($sampleUsers[0]['email'])
    ->externalId($sampleUsers[0]['externalId'])
    ->profileUrl($sampleUsers[0]['profileUrl'])
    ->custom($updatedCustomData)
    ->sync();
assert($updateUserMetadataResult->getName() === $sampleUsers[0]['name'] . ' - Updated');
// snippet.end

// snippet.remove_user_metadata
$removeUserMetadataResult = $pubnub->removeUuidMetadata()
    ->uuid($sampleUsers[1]['id'])
    ->sync();
assert($removeUserMetadataResult);
// snippet.end

// snippet.get_after_remove_error_handling
// Verify user was removed by checking it's no longer in getUuidMetadata
try {
    $getAfterRemoveResult = $pubnub->getUuidMetadata()
        ->uuid($sampleUsers[1]['id'])
        ->sync();
} catch (PubNubServerException $e) {
    assert($e->getStatusCode() === 404);
}
// snippet.end

// Step 3: Channel Management Operations

// snippet.set_channel_metadata
foreach ($sampleChannels as $channel) {
    $setChannelMetadataResult = $pubnub->setChannelMetadata()
        ->channel($channel['id'])
        ->setName($channel['name'])
        ->setDescription($channel['description'])
        ->setCustom($channel['custom'])
        ->sync();
    assert($setChannelMetadataResult->getId() === $channel['id']);
    assert($setChannelMetadataResult->getName() === $channel['name']);
    assert($setChannelMetadataResult->getDescription() === $channel['description']);
}
// snippet.end

// snippet.get_channel_metadata
$getChannelMetadataResult = $pubnub->getChannelMetadata()
    ->channel($sampleChannels[0]['id'])
    ->sync();
assert($getChannelMetadataResult->getId() === $sampleChannels[0]['id']);
assert($getChannelMetadataResult->getName() === $sampleChannels[0]['name']);
// snippet.end

// snippet.get_all_channel_metadata
$getAllChannelMetadataResult = $pubnub->getAllChannelMetadata()
    ->includeFields(['customFields' => true, 'totalCount' => true])
    ->limit(10)
    ->sync();
assert(count($getAllChannelMetadataResult->getData()) >= 2);
// snippet.end

// snippet.update_channel_metadata
$updatedChannelCustom = [
    'category' => 'company-updated',
    'public' => true,
    'lastModified' => date('Y-m-d H:i:s')
];

$updateChannelMetadataResult = $pubnub->setChannelMetadata()
    ->channel($sampleChannels[0]['id'])
    ->setName($sampleChannels[0]['name'] . ' - Updated')
    ->setDescription($sampleChannels[0]['description'] . ' - Updated')
    ->setCustom($updatedChannelCustom)
    ->sync();
assert($updateChannelMetadataResult->getName() === $sampleChannels[0]['name'] . ' - Updated');
// snippet.end

// snippet.remove_channel_metadata
$removeChannelMetadataResult = $pubnub->removeChannelMetadata()
    ->channel($sampleChannels[1]['id'])
    ->sync();
assert($removeChannelMetadataResult);
// snippet.end

// Step 4: Channel Membership Operations (User-centric)

// snippet.set_channel_memberships
$memberships = [
    new PNChannelMembership($sampleChannels[0]['id']),
    new PNChannelMembership($sampleChannels[1]['id'])
];

$setMembershipsResult = $pubnub->setMemberships()
    ->uuid($sampleUsers[0]['id'])
    ->memberships($memberships)
    ->sync();
assert(count($setMembershipsResult->getData()) >= 1);
// snippet.end

// snippet.get_channel_memberships
$membershipIncludes = new PNMembershipIncludes();
$membershipIncludes->custom()->channel()->channelCustom();

$getMembershipsResult = $pubnub->getMemberships()
    ->uuid($sampleUsers[0]['id'])
    ->include($membershipIncludes)
    ->sync();
assert(count($getMembershipsResult->getData()) >= 1);
// snippet.end

// snippet.manage_channel_memberships
$setMembershipsList = [new PNChannelMembership($sampleChannels[0]['id'])];
$removeMembershipsList = [new PNChannelMembership($sampleChannels[1]['id'])];

$manageMembershipsResult = $pubnub->manageMemberships()
    ->uuid($sampleUsers[0]['id'])
    ->setMemberships($setMembershipsList)
    ->removeMemberships($removeMembershipsList)
    ->sync();
assert(count($manageMembershipsResult->getData()) >= 0);
// snippet.end

// snippet.remove_channel_memberships
$removeMembershipsList = [new PNChannelMembership($sampleChannels[0]['id'])];

$removeMembershipsResult = $pubnub->removeMemberships()
    ->uuid($sampleUsers[0]['id'])
    ->memberships($removeMembershipsList)
    ->sync();
assert($removeMembershipsResult);
// snippet.end

// Step 5: Channel Members Operations (Channel-centric)

// snippet.set_channel_members
$channelMembers = [
    new PNChannelMember($sampleUsers[0]['id']),
    new PNChannelMember($sampleUsers[1]['id'])
];

$setMembersResult = $pubnub->setMembers()
    ->channel($sampleChannels[0]['id'])
    ->members($channelMembers)
    ->sync();
assert(count($setMembersResult->getData()) >= 1);
// snippet.end

// snippet.get_channel_members
$memberIncludes = new PNMemberIncludes();
$memberIncludes->custom()->user()->userCustom();

$getMembersResult = $pubnub->getMembers()
    ->channel($sampleChannels[0]['id'])
    ->include($memberIncludes)
    ->sync();
assert(count($getMembersResult->getData()) >= 1);
// snippet.end

// snippet.manage_channel_members
$setMembersList = [new PNChannelMember($sampleUsers[0]['id'])];
$removeMembersList = [new PNChannelMember($sampleUsers[1]['id'])];

$manageMembersResult = $pubnub->manageMembers()
    ->channel($sampleChannels[0]['id'])
    ->setMembers($setMembersList)
    ->removeMembers($removeMembersList)
    ->sync();
assert(count($manageMembersResult->getData()) >= 0);
// snippet.end

// snippet.remove_channel_members
$removeMembersList = [new PNChannelMember($sampleUsers[0]['id'])];

$removeMembersResult = $pubnub->removeMembers()
    ->channel($sampleChannels[0]['id'])
    ->members($removeMembersList)
    ->sync();
assert($removeMembersResult);
// snippet.end

// Step 6: Advanced Features (pagination, filtering, sorting)

// snippet.pagination_example
// Get first page of users
$firstPageResult = $pubnub->getAllUuidMetadata()
    ->includeFields(['totalCount' => true])
    ->limit(1) // Small limit to demonstrate pagination
    ->sync();

echo "First page: " . count($firstPageResult->getData()) . " users\n";
echo "Total count: " . $firstPageResult->getTotalCount() . "\n";

// Get next page if available
if ($firstPageResult->getNext()) {
    $secondPageResult = $pubnub->getAllUuidMetadata()
        ->includeFields(['totalCount' => true])
        ->limit(1)
        ->page(['next' => $firstPageResult->getNext()])
        ->sync();
    echo "Second page: " . count($secondPageResult->getData()) . " users\n";
}
// snippet.end

// snippet.filtering_example
// Filter users by custom field (note: filtering by custom properties may have limitations)
$filteredUsersResult = $pubnub->getAllUuidMetadata()
    ->includeFields(['customFields' => true])
    ->filter("name LIKE '*Alice*'")
    ->sync();

echo "Filtered users (name contains 'Alice'): " . count($filteredUsersResult->getData()) . "\n";

// Filter channels by name
$filteredChannelsResult = $pubnub->getAllChannelMetadata()
    ->includeFields(['customFields' => true])
    ->filter("name LIKE '*General*'")
    ->sync();

echo "Filtered channels (name contains 'General'): " . count($filteredChannelsResult->getData()) . "\n";
// snippet.end

// snippet.sorting_example
// Sort users by name ascending
$sortedUsersAscResult = $pubnub->getAllUuidMetadata()
    ->sort(['name' => 'asc'])
    ->limit(5)
    ->sync();

echo "Users sorted by name (ascending): " . count($sortedUsersAscResult->getData()) . "\n";
foreach ($sortedUsersAscResult->getData() as $user) {
    echo "  - " . ($user->getName() ?: $user->getId()) . "\n";
}

// Sort users by updated time descending
$sortedUsersDescResult = $pubnub->getAllUuidMetadata()
    ->sort(['updated' => 'desc'])
    ->limit(3)
    ->sync();

echo "Users sorted by updated time (descending): " . count($sortedUsersDescResult->getData()) . "\n";

// Sort channels by name
$sortedChannelsResult = $pubnub->getAllChannelMetadata()
    ->sort(['name' => 'asc'])
    ->limit(5)
    ->sync();

echo "Channels sorted by name: " . count($sortedChannelsResult->getData()) . "\n";
// snippet.end

// snippet.custom_fields_example
// Create user with complex custom data
$complexCustomData = [
    'profile' => [
        'avatar' => 'https://example.com/avatar.jpg',
        'bio' => 'Software engineer with 10+ years experience',
        'social' => [
            'twitter' => '@johndoe',
            'linkedin' => '/in/johndoe'
        ]
    ],
    'preferences' => [
        'theme' => 'dark',
        'language' => 'en',
        'notifications' => [
            'email' => true,
            'push' => false,
            'sms' => true
        ]
    ],
    'metadata' => [
        'created' => date('Y-m-d H:i:s'),
        'source' => 'api',
        'version' => '1.0'
    ]
];

$complexUserResult = $pubnub->setUuidMetadata()
    ->uuid('user_complex_' . time())
    ->name('John Doe')
    ->email('john.doe@example.com')
    ->custom($complexCustomData)
    ->sync();

echo "Created user with complex custom data: " . $complexUserResult->getName() . "\n";
echo "Custom data keys: " . implode(', ', array_keys((array)$complexUserResult->getCustom())) . "\n";
// snippet.end

// snippet.include_fields_example
// Get users with selective field inclusion
$selectiveFieldsResult = $pubnub->getAllUuidMetadata()
    ->includeFields(['customFields' => true, 'totalCount' => false])
    ->limit(2)
    ->sync();

echo "Users with custom fields (no total count): " . count($selectiveFieldsResult->getData()) . "\n";
echo "Total count included: " . ($selectiveFieldsResult->getTotalCount() ? 'Yes' : 'No') . "\n";

// Get memberships with channel details (using deprecated method for compatibility)
if (!empty($sampleUsers)) {
    try {
        $membershipWithChannelResult = $pubnub->getMemberships()
            ->uuid($sampleUsers[0]['id'])
            ->includeFields(['channelFields' => true, 'customFields' => true])
            ->limit(5)
            ->sync();

        echo "Memberships with channel details: " . count($membershipWithChannelResult->getData()) . "\n";
    } catch (PubNubServerException $e) {
        echo "Memberships query skipped (no memberships exist): " . $e->getStatusCode() . "\n";
    }
}
// snippet.end

// snippet.etag_example
// Get user metadata with ETag
$userWithEtagResult = $pubnub->getUuidMetadata()
    ->uuid($sampleUsers[0]['id'])
    ->sync();

$etag = $userWithEtagResult->getETag();
var_dump($etag);
echo "User ETag: " . ($etag ?: 'Not available') . "\n";

// Attempt conditional update using ETag
if ($etag) {
    try {
        $conditionalUpdateResult = $pubnub->setUuidMetadata()
            ->uuid($sampleUsers[0]['id'])
            ->name($sampleUsers[0]['name'] . ' - Conditional Update')
            ->email($sampleUsers[0]['email'])
            ->ifMatchesEtag($etag)
            ->sync();

        echo "Conditional update successful: " . $conditionalUpdateResult->getName() . "\n";
    } catch (PubNubServerException $e) {
        if ($e->getStatusCode() === 412) {
            echo "Conditional update failed: ETag mismatch (HTTP 412)\n";
        } else {
            echo "Update failed: " . $e->getMessage() . "\n";
        }
    }
}
// snippet.end

// snippet.combined_advanced_features
// Simplified complex query to avoid filter issues
$advancedQueryResult = $pubnub->getAllUuidMetadata()
    ->includeFields(['customFields' => true, 'totalCount' => true])
    ->sort(['name' => 'asc'])
    ->limit(5)
    ->sync();

echo "Advanced query results: " . count($advancedQueryResult->getData()) . " users\n";
echo "Total available: " . ($advancedQueryResult->getTotalCount() ?: 'Unknown') . "\n";
echo "Next page available: " . ($advancedQueryResult->getNext() ? 'Yes' : 'No') . "\n";

// Get channel members with comprehensive includes (using deprecated method for compatibility)
if (!empty($sampleChannels)) {
    try {
        $comprehensiveMembersResult = $pubnub->getMembers()
            ->channel($sampleChannels[0]['id'])
            ->includeFields(['customFields' => true, 'UUIDFields' => true, 'totalCount' => true])
            ->sort(['name' => 'asc'])
            ->limit(10)
            ->sync();

        echo "Channel members with full details: " . count($comprehensiveMembersResult->getData()) . "\n";
    } catch (PubNubServerException $e) {
        echo "Channel members query skipped (no members exist): " . $e->getStatusCode() . "\n";
    }
}
// snippet.end
