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

// The entity and relationship classes below have to exist in the key's class registry, which is
// managed in the PubNub Admin Portal rather than through the SDK.
$entityClass = getenv('DATASYNC_ENTITY_CLASS') ?: 'vehicle';
$relationshipClass = getenv('DATASYNC_RELATIONSHIP_CLASS') ?: 'owns';

$config = new PNConfiguration();
$config->setSubscribeKey($subscribeKey);
$config->setPublishKey($publishKey);
$config->setUserId("php-datasync-sample-" . time());

$pubnub = new PubNub($config);
// snippet.end

$vehicleId = 'vehicle_' . time();
$userId = 'user_' . time();
$channelId = 'channel_' . time();

// snippet.create_entity
$created = $pubnub->dataSync()
    ->createEntity()
    ->entityId($vehicleId)
    ->entityClass($entityClass)
    ->entityClassVersion(1)
    ->status('active')
    ->payload([
        'make' => 'Toyota',
        'model' => 'Camry',
        'year' => 2024,
    ])
    ->sync();

printf("Created %s with ETag %s\n", $created->getId(), $created->getETag());
// snippet.end

// snippet.get_entity
$entity = $pubnub->dataSync()
    ->getEntity()
    ->entityId($vehicleId)
    ->sync();

printf("Status: %s, make: %s\n", $entity->getData()->getStatus(), $entity->getData()->getPayload()['make']);
// snippet.end

// snippet.update_entity
// A patch changes only the fields it names. Top-level fields are addressed directly, while the
// user-defined payload lives under /payload.
$patch = (new PNDataSyncPatch())
    ->replace('/status', 'inactive')
    ->add('/payload/color', 'blue');

$updated = $pubnub->dataSync()
    ->updateEntity()
    ->entityId($vehicleId)
    ->ifMatchesETag($entity->getETag())
    ->patch($patch)
    ->sync();

printf("Status is now %s\n", $updated->getData()->getStatus());
// snippet.end

// snippet.set_entity
// Unlike a patch, a set replaces the whole record; anything left out is cleared.
$replaced = $pubnub->dataSync()
    ->setEntity()
    ->entityId($vehicleId)
    ->entityClassVersion(1)
    ->status('active')
    ->payload([
        'make' => 'Toyota',
        'model' => 'Corolla',
        'year' => 2025,
    ])
    ->sync();

printf("Replaced, new ETag %s\n", $replaced->getETag());
// snippet.end

// snippet.handle_stale_etag
try {
    $pubnub->dataSync()
        ->updateEntity()
        ->entityId($vehicleId)
        ->ifMatchesETag($entity->getETag())
        ->patch((new PNDataSyncPatch())->replace('/status', 'archived'))
        ->sync();
} catch (PubNubServerException $exception) {
    if ($exception->getStatusCode() === 412) {
        echo "Someone else changed the record first; re-read it and retry.\n";
    } else {
        throw $exception;
    }
}
// snippet.end

// snippet.list_entities
$cursor = null;

do {
    $page = $pubnub->dataSync()->getEntities()
        ->entityClass($entityClass)
        ->limit(50);

    if ($cursor !== null) {
        $page->cursor($cursor);
    }

    $result = $page->sync();

    foreach ($result->getData() as $item) {
        printf("- %s (%s)\n", $item->getId(), $item->getStatus());
    }

    $cursor = $result->getPage() !== null && $result->getPage()->hasNext()
        ? $result->getPage()->getNextCursor()
        : null;
} while ($cursor !== null);
// snippet.end

// snippet.filter_and_sort
$activeVehicles = $pubnub->dataSync()->getEntities()
    ->entityClass($entityClass)
    ->filter("status == 'active'")
    ->sort(['createdAt' => 'desc'])
    ->limit(10)
    ->sync();

printf("Found %d active vehicles\n", $activeVehicles->count());
// snippet.end

// snippet.users_and_channels
// Users and channels are predefined entity classes, so entityClass can be left out.
$user = $pubnub->dataSync()->createUser()
    ->userId($userId)
    ->entityClassVersion(1)
    ->payload(['name' => 'Alice Johnson', 'email' => 'alice@example.com'])
    ->sync();

$channel = $pubnub->dataSync()->createChannel()
    ->channelId($channelId)
    ->entityClassVersion(1)
    ->payload(['name' => 'General Discussion'])
    ->sync();

printf("Created user %s and channel %s\n", $user->getId(), $channel->getId());
// snippet.end

// snippet.memberships
$membership = $pubnub->dataSync()->createMembership()
    ->channelId($channelId)
    ->userId($userId)
    ->relationshipClassVersion(1)
    ->payload(['role' => 'moderator'])
    ->sync();

$usersChannels = $pubnub->dataSync()->getMemberships()
    ->userId($userId)
    ->sync();

printf("%s belongs to %d channel(s)\n", $userId, $usersChannels->count());
// snippet.end

// snippet.relationships
$relationship = $pubnub->dataSync()->createRelationship()
    ->entityAId($userId)
    ->entityBId($vehicleId)
    ->relationshipClass($relationshipClass)
    ->relationshipClassVersion(1)
    ->payload(['since' => '2026-01-01'])
    ->sync();

$owned = $pubnub->dataSync()->getRelationships()
    ->relationshipClass($relationshipClass)
    ->entityAId($userId)
    ->sync();

printf("%s owns %d vehicle(s)\n", $userId, $owned->count());
// snippet.end

// snippet.error_handling
// envelope() returns the result and the status side by side instead of throwing.
$envelope = $pubnub->dataSync()->getEntity()->entityId('does-not-exist')->envelope();

if ($envelope->isError()) {
    printf("Lookup failed with HTTP %d\n", $envelope->getStatus()->getStatusCode());
} else {
    printf("Found %s\n", $envelope->getResult()->getId());
}
// snippet.end

// snippet.delete
$pubnub->dataSync()->deleteRelationship()->relationshipId($relationship->getId())->sync();
$pubnub->dataSync()->deleteMembership()->membershipId($membership->getId())->sync();
$pubnub->dataSync()->deleteEntity()->entityId($vehicleId)->sync();
$pubnub->dataSync()->deleteUser()->userId($userId)->sync();
$pubnub->dataSync()->deleteChannel()->channelId($channelId)->sync();

echo "Cleaned up\n";
// snippet.end

// snippet.events
// DataSync change notifications arrive over the subscribe stream on a channel named after the
// record's identifier. PHP's subscribe loop blocks forever, so this only makes sense in a
// long-running CLI process, never inside a web request.
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
            "%s %s %s (class %s v%d)\n",
            $event->getEvent(),
            $event->getType(),
            $event->getId(),
            $event->getClassName(),
            $event->getClassVersion()
        );

        if ($event->getEntity() !== null) {
            print_r($event->getEntity()->getPayload());
        }
    }
}

// $pubnub->addListener(new DataSyncListener());
// $pubnub->subscribe()->channels($vehicleId)->execute();
// snippet.end

// snippet.access_control
// Granting DataSync access needs a secret key, so it belongs on a server you control rather than
// in the client that will use the token.
$secretKey = getenv('SECRET_KEY');

if ($secretKey) {
    $adminConfig = new PNConfiguration();
    $adminConfig->setSubscribeKey($subscribeKey);
    $adminConfig->setPublishKey($publishKey);
    $adminConfig->setSecretKey($secretKey);
    $adminConfig->setUserId('php-datasync-admin');

    $admin = new PubNub($adminConfig);

    // DataSync reads are gated on "get" and writes on "update". The read and write bits that App
    // Context grants are not what it looks at, so a token granted those permits nothing here.
    // A projection names the subset of fields the holder may see. "__default__" is the full record.
    $token = $admin->grantToken()
        ->ttl(60)
        ->authorizedUuid($userId)
        ->addDataSyncEntityResources([$vehicleId => ['get' => true, 'update' => true]])
        ->addDataSyncMembershipPatterns(['^' . $userId . ':.*$' => ['get' => true]])
        ->dataSyncProjections([
            'resources' => ['entities' => [$vehicleId => '__default__']],
            'patterns' => ['memberships' => ['^' . $userId . ':.*$' => 'summary']],
        ])
        ->sync();

    // Reading the grants back out of a token, for logging or for checking what a client was given.
    $parsed = $admin->parseToken($token);

    $vehiclePermissions = $parsed->getDataSyncEntityResource($vehicleId);
    printf(
        "%s: get=%s update=%s delete=%s\n",
        $vehicleId,
        var_export($vehiclePermissions->hasGet(), true),
        var_export($vehiclePermissions->hasUpdate(), true),
        var_export($vehiclePermissions->hasDelete(), true)
    );

    $projections = $parsed->getDataSyncProjections();

    if ($projections !== null) {
        printf(
            "entity projection: %s, membership pattern projection: %s\n",
            $projections->getResources()->getEntityProjection($vehicleId),
            $projections->getPatterns()->getMembershipProjection('^' . $userId . ':.*$')
        );
    }
}
// snippet.end
