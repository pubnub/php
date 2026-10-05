<?php

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
namespace PubNub\Examples;

// Include Composer autoloader (adjust path if needed)
require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\DataSync\PNDataSyncPatch;

// snippet.setup
$publishKey = getenv('PUBLISH_KEY') ?: 'demo';
$subscribeKey = getenv('SUBSCRIBE_KEY') ?: 'demo';
$secretKey = getenv('SECRET_KEY') ?: 'demo';

$config = new PNConfiguration();
$config->setSubscribeKey($subscribeKey);
$config->setPublishKey($publishKey);
$config->setSecretKey($secretKey);
$config->setUserId('php-datasync-sample');

$pubnub = new PubNub($config);
// snippet.end

// snippet.create_user_basic_usage
$result = $pubnub->dataSync()->createUser()
    ->userId('user-alice')
    ->entityClassVersion(1)
    ->payload(['name' => 'Alice', 'type' => 'shopper'])
    ->sync();

printf("Created %s with ETag %s\n", $result->getId(), $result->getETag());
// snippet.end

// snippet.get_user_basic_usage
$result = $pubnub->dataSync()->getUser()
    ->userId('user-alice')
    ->sync();

printf("Fetched %s\n", $result->getId());
// snippet.end

// snippet.get_users_basic_usage
$result = $pubnub->dataSync()->getUsers()
    ->sync();

printf("Fetched %d users\n", $result->count());
// snippet.end

// snippet.set_user_basic_usage
$result = $pubnub->dataSync()->setUser()
    ->userId('user-alice')
    ->entityClassVersion(1)
    ->payload(['name' => 'Alice B.', 'type' => 'shopper'])
    ->sync();

printf("Replaced user, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.update_user_basic_usage
$patch = (new PNDataSyncPatch())
    ->replace('/payload/name', 'Alice B.');

$result = $pubnub->dataSync()->updateUser()
    ->userId('user-alice')
    ->patch($patch)
    ->sync();

printf("Patched user, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.remove_user_basic_usage
$pubnub->dataSync()->deleteUser()
    ->userId('user-alice')
    ->sync();
// snippet.end

// snippet.get_users_filter_fast
$result = $pubnub->dataSync()->getUsers()
    ->filterFast('type == "shopper"')
    ->sync();

printf("Found %d users\n", $result->count());
// snippet.end

// snippet.get_users_filter
$result = $pubnub->dataSync()->getUsers()
    ->filter('name LIKE "*Alice*"')
    ->sync();

printf("Found %d users\n", $result->count());
// snippet.end

// snippet.get_users_pagination
$cursor = null;

do {
    $request = $pubnub->dataSync()->getUsers()
        ->limit(50);

    if ($cursor !== null) {
        $request->cursor($cursor);
    }

    $result = $request->sync();

    foreach ($result->getData() as $item) {
        printf("%s\n", $item->getId());
    }

    $page = $result->getPage();
    $cursor = $page !== null && $page->hasNext() ? $page->getNextCursor() : null;
} while ($cursor !== null);
// snippet.end

// snippet.create_channel_basic_usage
$result = $pubnub->dataSync()->createChannel()
    ->channelId('channel-summer-sale')
    ->entityClassVersion(1)
    ->payload(['name' => 'Summer Sale', 'type' => 'promotion'])
    ->sync();

printf("Created %s with ETag %s\n", $result->getId(), $result->getETag());
// snippet.end

// snippet.get_channel_basic_usage
$result = $pubnub->dataSync()->getChannel()
    ->channelId('channel-summer-sale')
    ->sync();

printf("Fetched %s\n", $result->getId());
// snippet.end

// snippet.get_channels_basic_usage
$result = $pubnub->dataSync()->getChannels()
    ->sync();

printf("Fetched %d channels\n", $result->count());
// snippet.end

// snippet.set_channel_basic_usage
$result = $pubnub->dataSync()->setChannel()
    ->channelId('channel-summer-sale')
    ->entityClassVersion(1)
    ->payload(['name' => 'Summer Sale 2026', 'type' => 'promotion'])
    ->sync();

printf("Replaced channel, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.update_channel_basic_usage
$patch = (new PNDataSyncPatch())
    ->replace('/payload/name', 'Summer Sale 2026');

$result = $pubnub->dataSync()->updateChannel()
    ->channelId('channel-summer-sale')
    ->patch($patch)
    ->sync();

printf("Patched channel, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.remove_channel_basic_usage
$pubnub->dataSync()->deleteChannel()
    ->channelId('channel-summer-sale')
    ->sync();
// snippet.end

// snippet.get_channels_filter_fast
$result = $pubnub->dataSync()->getChannels()
    ->filterFast('type == "promotion"')
    ->sync();

printf("Found %d channels\n", $result->count());
// snippet.end

// snippet.get_channels_filter
$result = $pubnub->dataSync()->getChannels()
    ->filter('name LIKE "*Summer*"')
    ->sync();

printf("Found %d channels\n", $result->count());
// snippet.end

// snippet.get_channels_pagination
$cursor = null;

do {
    $request = $pubnub->dataSync()->getChannels()
        ->limit(50);

    if ($cursor !== null) {
        $request->cursor($cursor);
    }

    $result = $request->sync();

    foreach ($result->getData() as $item) {
        printf("%s\n", $item->getId());
    }

    $page = $result->getPage();
    $cursor = $page !== null && $page->hasNext() ? $page->getNextCursor() : null;
} while ($cursor !== null);
// snippet.end

// snippet.create_membership_basic_usage
$result = $pubnub->dataSync()->createMembership()
    ->membershipId('membership-alice-summer-sale')
    ->channelId('channel-summer-sale')
    ->userId('user-alice')
    ->relationshipClassVersion(1)
    ->payload(['role' => 'viewer'])
    ->sync();

printf("Created %s with ETag %s\n", $result->getId(), $result->getETag());
// snippet.end

// snippet.get_membership_basic_usage
$result = $pubnub->dataSync()->getMembership()
    ->membershipId('membership-alice-summer-sale')
    ->sync();

printf("Fetched %s\n", $result->getId());
// snippet.end

// snippet.get_memberships_basic_usage
$result = $pubnub->dataSync()->getMemberships()
    ->userId('user-alice')
    ->sync();

printf("Fetched %d memberships\n", $result->count());
// snippet.end

// snippet.set_membership_basic_usage
$result = $pubnub->dataSync()->setMembership()
    ->membershipId('membership-alice-summer-sale')
    ->relationshipClassVersion(1)
    ->payload(['role' => 'moderator'])
    ->sync();

printf("Replaced membership, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.update_membership_basic_usage
$patch = (new PNDataSyncPatch())
    ->replace('/payload/role', 'moderator');

$result = $pubnub->dataSync()->updateMembership()
    ->membershipId('membership-alice-summer-sale')
    ->patch($patch)
    ->sync();

printf("Patched membership, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.remove_membership_basic_usage
$pubnub->dataSync()->deleteMembership()
    ->membershipId('membership-alice-summer-sale')
    ->sync();
// snippet.end

// snippet.get_memberships_filter_fast
$result = $pubnub->dataSync()->getMemberships()
    ->userId('user-alice')
    ->filterFast('role == "viewer"')
    ->sync();

printf("Found %d memberships\n", $result->count());
// snippet.end

// snippet.get_memberships_filter
$result = $pubnub->dataSync()->getMemberships()
    ->userId('user-alice')
    ->filter('role LIKE "*view*"')
    ->sync();

printf("Found %d memberships\n", $result->count());
// snippet.end

// snippet.get_memberships_pagination
$cursor = null;

do {
    $request = $pubnub->dataSync()->getMemberships()
    ->userId('user-alice')
        ->limit(50);

    if ($cursor !== null) {
        $request->cursor($cursor);
    }

    $result = $request->sync();

    foreach ($result->getData() as $item) {
        printf("%s\n", $item->getId());
    }

    $page = $result->getPage();
    $cursor = $page !== null && $page->hasNext() ? $page->getNextCursor() : null;
} while ($cursor !== null);
// snippet.end

// snippet.get_memberships_by_channel_id
$result = $pubnub->dataSync()->getMemberships()
    ->channelId('channel-summer-sale')
    ->sync();

printf("Found %d members\n", $result->count());
// snippet.end

// snippet.create_entity_basic_usage
$result = $pubnub->dataSync()->createEntity()
    ->entityId('product-sneaker-42')
    ->entityClass('product')
    ->entityClassVersion(1)
    ->payload(['name' => 'Retro Sneaker', 'price' => 89.99, 'stock' => 12])
    ->sync();

printf("Created %s with ETag %s\n", $result->getId(), $result->getETag());
// snippet.end

// snippet.get_entity_basic_usage
$result = $pubnub->dataSync()->getEntity()
    ->entityId('product-sneaker-42')
    ->sync();

printf("Fetched %s\n", $result->getId());
// snippet.end

// snippet.get_entities_basic_usage
$result = $pubnub->dataSync()->getEntities()
    ->entityClass('product')
    ->sync();

printf("Fetched %d entities\n", $result->count());
// snippet.end

// snippet.set_entity_basic_usage
$result = $pubnub->dataSync()->setEntity()
    ->entityId('product-sneaker-42')
    ->entityClassVersion(1)
    ->payload(['name' => 'Retro Sneaker', 'price' => 79.99, 'stock' => 8])
    ->sync();

printf("Replaced entity, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.update_entity_basic_usage
$patch = (new PNDataSyncPatch())
    ->replace('/payload/price', 79.99);

$result = $pubnub->dataSync()->updateEntity()
    ->entityId('product-sneaker-42')
    ->patch($patch)
    ->sync();

printf("Patched entity, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.remove_entity_basic_usage
$pubnub->dataSync()->deleteEntity()
    ->entityId('product-sneaker-42')
    ->sync();
// snippet.end

// snippet.get_entities_filter_fast
$result = $pubnub->dataSync()->getEntities()
    ->entityClass('product')
    ->filterFast('price < 100')
    ->sync();

printf("Found %d entities\n", $result->count());
// snippet.end

// snippet.get_entities_filter
$result = $pubnub->dataSync()->getEntities()
    ->entityClass('product')
    ->filter('name LIKE "*sneaker*"')
    ->sync();

printf("Found %d entities\n", $result->count());
// snippet.end

// snippet.get_entities_pagination
$cursor = null;

do {
    $request = $pubnub->dataSync()->getEntities()
    ->entityClass('product')
        ->limit(50);

    if ($cursor !== null) {
        $request->cursor($cursor);
    }

    $result = $request->sync();

    foreach ($result->getData() as $item) {
        printf("%s\n", $item->getId());
    }

    $page = $result->getPage();
    $cursor = $page !== null && $page->hasNext() ? $page->getNextCursor() : null;
} while ($cursor !== null);
// snippet.end

// snippet.update_entity_multiple_operations
$entity = $pubnub->dataSync()->getEntity()
    ->entityId('product-sneaker-42')
    ->sync();

$patch = (new PNDataSyncPatch())
    ->test('/status', 'active')
    ->add('/payload/tags', ['sale'])
    ->replace('/payload/price', 79.99)
    ->remove('/payload/tempFlag')
    ->move('/payload/oldTag', '/payload/tag')
    ->copy('/payload/price', '/payload/msrp');

$result = $pubnub->dataSync()->updateEntity()
    ->entityId('product-sneaker-42')
    ->ifMatchesETag($entity->getETag())
    ->patch($patch)
    ->sync();

printf("Patched, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.create_relationship_basic_usage
$result = $pubnub->dataSync()->createRelationship()
    ->relationshipId('rel-bob-owns-sneaker-42')
    ->entityAId('seller-bob')
    ->entityBId('product-sneaker-42')
    ->relationshipClass('ProductOwner')
    ->relationshipClassVersion(1)
    ->payload(['since' => '2026-07-13'])
    ->sync();

printf("Created %s with ETag %s\n", $result->getId(), $result->getETag());
// snippet.end

// snippet.get_relationship_basic_usage
$result = $pubnub->dataSync()->getRelationship()
    ->relationshipId('rel-bob-owns-sneaker-42')
    ->sync();

printf("Fetched %s\n", $result->getId());
// snippet.end

// snippet.get_relationships_basic_usage
$result = $pubnub->dataSync()->getRelationships()
    ->relationshipClass('ProductOwner')
    ->sync();

printf("Fetched %d relationships\n", $result->count());
// snippet.end

// snippet.set_relationship_basic_usage
$result = $pubnub->dataSync()->setRelationship()
    ->relationshipId('rel-bob-owns-sneaker-42')
    ->relationshipClassVersion(1)
    ->payload(['since' => '2026-07-13', 'tier' => 'gold'])
    ->sync();

printf("Replaced relationship, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.update_relationship_basic_usage
$patch = (new PNDataSyncPatch())
    ->replace('/payload/tier', 'platinum');

$result = $pubnub->dataSync()->updateRelationship()
    ->relationshipId('rel-bob-owns-sneaker-42')
    ->patch($patch)
    ->sync();

printf("Patched relationship, new ETag %s\n", $result->getETag());
// snippet.end

// snippet.remove_relationship_basic_usage
$pubnub->dataSync()->deleteRelationship()
    ->relationshipId('rel-bob-owns-sneaker-42')
    ->sync();
// snippet.end

// snippet.get_relationships_filter_fast
$result = $pubnub->dataSync()->getRelationships()
    ->relationshipClass('ProductOwner')
    ->filterFast('since >= "2026-01-01"')
    ->sync();

printf("Found %d relationships\n", $result->count());
// snippet.end

// snippet.get_relationships_filter
$result = $pubnub->dataSync()->getRelationships()
    ->relationshipClass('ProductOwner')
    ->filter('since LIKE "2026*"')
    ->sync();

printf("Found %d relationships\n", $result->count());
// snippet.end

// snippet.get_relationships_pagination
$cursor = null;

do {
    $request = $pubnub->dataSync()->getRelationships()
    ->relationshipClass('ProductOwner')
        ->limit(50);

    if ($cursor !== null) {
        $request->cursor($cursor);
    }

    $result = $request->sync();

    foreach ($result->getData() as $item) {
        printf("%s\n", $item->getId());
    }

    $page = $result->getPage();
    $cursor = $page !== null && $page->hasNext() ? $page->getNextCursor() : null;
} while ($cursor !== null);
// snippet.end

// snippet.get_relationships_by_entity_b_id
$result = $pubnub->dataSync()->getRelationships()
    ->relationshipClass('ProductOwner')
    ->entityBId('product-sneaker-42')
    ->sync();

printf("Found %d relationships\n", $result->count());
// snippet.end

// snippet.data_sync_event_listener
class DataSyncListener extends SubscribeCallback
{
    public function status($pubnub, $status)
    {
    }

    public function message($pubnub, $message)
    {
    }

    public function presence($pubnub, $presence)
    {
    }

    public function dataSyncEvent($pubnub, $event)
    {
        printf(
            "%s %s %s (class %s)\n",
            $event->getEvent(),
            $event->getType(),
            $event->getId(),
            $event->getClassName()
        );
    }
}

$pubnub->addListener(new DataSyncListener());
$pubnub->subscribe()->channels('product-sneaker-42')->execute();
// snippet.end

// snippet.subscribe_to_projection_channels
class ProjectionListener extends SubscribeCallback
{
    public function status($pubnub, $status)
    {
    }

    public function message($pubnub, $message)
    {
    }

    public function presence($pubnub, $presence)
    {
    }

    public function dataSyncEvent($pubnub, $event)
    {
        printf(
            "%s %s %s (class %s)\n",
            $event->getEvent(),
            $event->getType(),
            $event->getId(),
            $event->getClassName()
        );
    }
}

$pubnub->addListener(new ProjectionListener());
$pubnub->subscribe()
    ->channels(['product-sneaker-42', '__admin__product-sneaker-42'])
    ->execute();
// snippet.end

// snippet.grant_token_data_sync
$token = $pubnub->grantToken()
    ->ttl(60)
    ->authorizedUuid('my-authorized-uuid')
    ->addDataSyncEntityResources([
        'product-sneaker-42' => ['create' => true, 'get' => true, 'update' => true, 'delete' => true],
    ])
    ->addDataSyncMembershipPatterns([
        'membership-.*' => ['create' => true, 'get' => true, 'update' => true, 'delete' => true],
    ])
    ->addChannelResources(['channel-summer-sale' => ['get' => true]])
    ->sync();
// snippet.end

// snippet.grant_token_data_sync_projection
$token = $pubnub->grantToken()
    ->ttl(60)
    ->authorizedUuid('my-authorized-uuid')
    ->addDataSyncEntityResources(['product-sneaker-42' => ['create' => true, 'get' => true, 'update' => true]])
    ->dataSyncProjections(['resources' => ['entities' => ['product-sneaker-42' => 'admin']]])
    ->sync();
// snippet.end

// snippet.parse_token_data_sync
$parsed = $pubnub->parseToken($token);

$permissions = $parsed->getDataSyncEntityResource('product-sneaker-42');

if ($permissions !== false) {
    printf(
        "get=%s update=%s\n",
        var_export($permissions->hasGet(), true),
        var_export($permissions->hasUpdate(), true)
    );
}

$projections = $parsed->getDataSyncProjections();

if ($projections !== null) {
    printf("projection: %s\n", $projections->getResources()->getEntityProjection('product-sneaker-42'));
}
// snippet.end

// snippet.handle_stale_etag
$entity = $pubnub->dataSync()->getEntity()
    ->entityId('product-sneaker-42')
    ->sync();

try {
    $pubnub->dataSync()->updateEntity()
        ->entityId('product-sneaker-42')
        ->ifMatchesETag($entity->getETag())
        ->patch((new PNDataSyncPatch())->replace('/payload/stock', 8))
        ->sync();
} catch (PubNubServerException $exception) {
    if ($exception->getStatusCode() === 412) {
        echo "The entity changed since you read it. Read it again and retry.\n";
    } else {
        throw $exception;
    }
}
// snippet.end

// snippet.error_handling
$envelope = $pubnub->dataSync()->getEntity()
    ->entityId('product-sneaker-42')
    ->envelope();

if ($envelope->isError()) {
    printf("Lookup failed with HTTP %d\n", $envelope->getStatus()->getStatusCode());
} else {
    printf("Found %s\n", $envelope->getResult()->getId());
}
// snippet.end
