<?php

namespace PubNubTests\integrational\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Models\Consumer\DataSync\PNDataSyncEventResult;
use PubNub\Models\Consumer\DataSync\PNDataSyncPatch;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTests\helpers\DataSyncEventCollector;

/**
 * DataSync change notifications delivered over a live subscribe connection.
 *
 * Writing a record makes the service publish an event on the channels named after the records it
 * concerns, which is the only way to see the whole subscribe path end to end: message type 5 is
 * recognised, the payload is parsed into a PNDataSyncEventResult and the result reaches
 * SubscribeCallback::dataSyncEvent(). Each test subscribes first, then writes from inside the
 * connect callback, because a PHP subscribe loop owns the calling thread.
 *
 * Skipped unless DATASYNC_SUBSCRIBE_KEY, DATASYNC_PUBLISH_KEY and DATASYNC_ENTITY_CLASS are set;
 * the relationship test additionally needs DATASYNC_ENTITY_RELATIONSHIP.
 */
class DataSyncEventTest extends TestCase
{
    private PubNub $pubnub;

    private string $entityClass;

    private string $relationshipClass;

    public function setUp(): void
    {
        parent::setUp();

        $subscribeKey = getenv('DATASYNC_SUBSCRIBE_KEY') ?: '';
        $publishKey = getenv('DATASYNC_PUBLISH_KEY') ?: '';
        $this->entityClass = getenv('DATASYNC_ENTITY_CLASS') ?: '';
        $this->relationshipClass = getenv('DATASYNC_ENTITY_RELATIONSHIP') ?: '';

        if ($subscribeKey === '' || $publishKey === '' || $this->entityClass === '') {
            $this->markTestSkipped(
                'Set DATASYNC_SUBSCRIBE_KEY, DATASYNC_PUBLISH_KEY and DATASYNC_ENTITY_CLASS to run the '
                    . 'DataSync event tests against a live keyset.'
            );
        }

        $config = new PNConfiguration();
        $config->setSubscribeKey($subscribeKey);
        $config->setPublishKey($publishKey);
        $config->setUuid('datasync-event-test-' . uniqid());

        $secretKey = getenv('DATASYNC_SECRET_KEY') ?: '';

        if ($secretKey !== '') {
            $config->setSecretKey($secretKey);
        }

        if ($origin = getenv('DATASYNC_ORIGIN')) {
            $config->setOrigin($origin);
        }

        $this->pubnub = new PubNub($config);
    }

    /**
     * Subscribes to $channel, runs $trigger once connected and returns the events it produced.
     *
     * @param callable $trigger
     * @param callable|null $accepts
     * @return PNDataSyncEventResult[]
     */
    private function captureEvents(
        string $channel,
        int $expected,
        callable $trigger,
        ?callable $accepts = null
    ): array {
        $collector = new DataSyncEventCollector($channel, $expected, $trigger, $accepts);
        $this->pubnub->addListener($collector);

        try {
            $this->pubnub->subscribe()->channels([$channel])->execute();
        } finally {
            $this->pubnub->removeListener($collector);
        }

        $events = $collector->getEvents();

        $this->assertCount(
            $expected,
            $events,
            sprintf('expected %d DataSync events on channel "%s", got %d', $expected, $channel, count($events))
        );

        return $events;
    }

    /**
     * Accepts the events of one record type, ignoring anything else sharing the channel.
     */
    private function ofType(string $type): callable
    {
        return static fn(PNDataSyncEventResult $event): bool
            => strtolower((string) $event->getType()) === $type;
    }

    /**
     * @param PNDataSyncEventResult[] $events
     */
    private function eventNamed(array $events, string $name): PNDataSyncEventResult
    {
        foreach ($events as $event) {
            if (strtolower((string) $event->getEvent()) === $name) {
                return $event;
            }
        }

        throw new \RuntimeException(sprintf('no "%s" event among the %d received', $name, count($events)));
    }

    public function testGenericEntityEvents(): void
    {
        $entityId = 'php-sdk-event-' . uniqid();

        $events = $this->captureEvents($entityId, 3, function () use ($entityId) {
            $this->pubnub->dataSync()->createEntity()
                ->entityId($entityId)
                ->entityClass($this->entityClass)
                ->entityClassVersion(1)
                ->status('active')
                ->payload(['make' => 'Toyota'])
                ->sync();

            $this->pubnub->dataSync()->updateEntity()
                ->entityId($entityId)
                ->patch((new PNDataSyncPatch())->replace('/status', 'patched'))
                ->sync();

            $this->pubnub->dataSync()->deleteEntity()->entityId($entityId)->sync();
        });

        $created = $this->eventNamed($events, 'create');

        $this->assertSame(PNDataSyncEventResult::SOURCE, $created->getSource());
        $this->assertSame('entity', strtolower((string) $created->getType()));
        $this->assertSame($entityId, $created->getChannel());
        $this->assertNotEmpty($created->getTimetoken());
        // The class name arrives bare, with the level telling built-in and custom classes apart.
        $this->assertSame($this->entityClass, $created->getClassName());
        $this->assertSame(1, $created->getClassVersion());
        $this->assertNotEmpty($created->getClassLevel());

        $entity = $created->getEntity();
        $this->assertNotNull($entity);
        $this->assertSame($entityId, $entity->getId());
        $this->assertSame('active', $entity->getStatus());
        $this->assertSame(['make' => 'Toyota'], $entity->getPayload());
        $this->assertSame($this->entityClass, $entity->getEntityClass());
        $this->assertNull($created->getRelationship());

        $updated = $this->eventNamed($events, 'update');
        $this->assertNotNull($updated->getEntity());
        $this->assertSame('patched', $updated->getEntity()->getStatus());

        $deleted = $this->eventNamed($events, 'delete');
        $this->assertSame($entityId, $deleted->getId());
        $this->assertNotEmpty($deleted->getDeletedAt());
        $this->assertNull($deleted->getEntity());
    }

    public function testGenericRelationshipEvents(): void
    {
        if ($this->relationshipClass === '') {
            $this->markTestSkipped('Set DATASYNC_ENTITY_RELATIONSHIP to run the relationship event test.');
        }

        $entityAId = $this->createEntity();
        $entityBId = $this->createEntity();
        $relationshipId = 'php-sdk-event-rel-' . uniqid();

        try {
            // A relationship event lands on the channels of the two records it links, not its own id.
            $trigger = function () use ($entityAId, $entityBId, $relationshipId): void {
                $this->pubnub->dataSync()->createRelationship()
                    ->relationshipId($relationshipId)
                    ->entityAId($entityAId)
                    ->entityBId($entityBId)
                    ->relationshipClass($this->relationshipClass)
                    ->relationshipClassVersion(1)
                    ->status('active')
                    ->payload(['role' => 'owner'])
                    ->sync();

                $this->pubnub->dataSync()->deleteRelationship()
                    ->relationshipId($relationshipId)
                    ->sync();
            };

            $events = $this->captureEvents($entityAId, 2, $trigger, $this->ofType('relationship'));

            $created = $this->eventNamed($events, 'create');

            $this->assertSame('relationship', strtolower((string) $created->getType()));
            $this->assertSame($entityAId, $created->getChannel());
            $this->assertSame($this->relationshipClass, $created->getClassName());
            $this->assertNull($created->getEntity());
            $this->assertNull($created->getMembership());

            $relationship = $created->getRelationship();
            $this->assertNotNull($relationship);
            $this->assertSame($relationshipId, $relationship->getId());
            $this->assertSame($entityAId, $relationship->getEntityAId());
            $this->assertSame($entityBId, $relationship->getEntityBId());
            $this->assertSame('active', $relationship->getStatus());
            $this->assertSame(['role' => 'owner'], $relationship->getPayload());

            $deleted = $this->eventNamed($events, 'delete');
            $this->assertSame($relationshipId, $deleted->getId());
            $this->assertNotEmpty($deleted->getDeletedAt());
            $this->assertNull($deleted->getRelationship());
        } finally {
            $this->pubnub->dataSync()->deleteEntity()->entityId($entityAId)->sync();
            $this->pubnub->dataSync()->deleteEntity()->entityId($entityBId)->sync();
        }
    }

    /**
     * User is a predefined entity class, so its events are entity-shaped and only the type differs.
     */
    public function testPredefinedUserEvents(): void
    {
        $userId = 'php-sdk-event-user-' . uniqid();

        $events = $this->captureEvents($userId, 2, function () use ($userId) {
            $this->pubnub->dataSync()->createUser()
                ->userId($userId)
                ->entityClassVersion(1)
                ->status('active')
                ->payload(['name' => 'Alice'])
                ->sync();

            $this->pubnub->dataSync()->deleteUser()->userId($userId)->sync();
        });

        $created = $this->eventNamed($events, 'create');

        $this->assertSame('user', strtolower((string) $created->getType()));
        $this->assertSame($userId, $created->getChannel());
        $this->assertNotEmpty($created->getClassName());
        $this->assertNotEmpty($created->getClassLevel());
        $this->assertNull($created->getRelationship());

        $user = $created->getEntity();
        $this->assertNotNull($user);
        $this->assertSame($userId, $user->getId());
        $this->assertSame('active', $user->getStatus());
        $this->assertSame(['name' => 'Alice'], $user->getPayload());

        $deleted = $this->eventNamed($events, 'delete');
        $this->assertSame($userId, $deleted->getId());
        $this->assertNotEmpty($deleted->getDeletedAt());
    }

    /**
     * Membership is a predefined relationship class between a Channel and a User, and its events
     * travel on the channels of both records it links.
     */
    public function testPredefinedMembershipEvents(): void
    {
        $channelId = $this->createChannel();
        $userId = $this->createUser();
        $membershipId = 'php-sdk-event-mem-' . uniqid();

        try {
            $trigger = function () use ($channelId, $userId, $membershipId): void {
                $this->pubnub->dataSync()->createMembership()
                    ->membershipId($membershipId)
                    ->channelId($channelId)
                    ->userId($userId)
                    ->relationshipClassVersion(1)
                    ->status('active')
                    ->payload(['role' => 'member'])
                    ->sync();

                $this->pubnub->dataSync()->deleteMembership()
                    ->membershipId($membershipId)
                    ->sync();
            };

            $events = $this->captureEvents($userId, 2, $trigger, $this->ofType('membership'));

            $created = $this->eventNamed($events, 'create');

            $this->assertSame('membership', strtolower((string) $created->getType()));
            $this->assertSame($userId, $created->getChannel());
            $this->assertNotEmpty($created->getClassLevel());
            $this->assertNull($created->getEntity());

            $membership = $created->getMembership();
            $this->assertNotNull($membership);
            $this->assertSame($membershipId, $membership->getId());
            $this->assertSame($channelId, $membership->getChannelId());
            $this->assertSame($userId, $membership->getUserId());
            $this->assertSame('active', $membership->getStatus());
            $this->assertSame(['role' => 'member'], $membership->getPayload());

            // The same record is also reported as a relationship, channel first and user second.
            $relationship = $created->getRelationship();
            $this->assertNotNull($relationship);
            $this->assertSame($channelId, $relationship->getEntityAId());
            $this->assertSame($userId, $relationship->getEntityBId());

            $deleted = $this->eventNamed($events, 'delete');
            $this->assertSame($membershipId, $deleted->getId());
            $this->assertNotEmpty($deleted->getDeletedAt());
            $this->assertNull($deleted->getMembership());
        } finally {
            $this->pubnub->dataSync()->deleteUser()->userId($userId)->sync();
            $this->pubnub->dataSync()->deleteChannel()->channelId($channelId)->sync();
        }
    }

    private function createEntity(): string
    {
        $entityId = 'php-sdk-event-' . uniqid();

        $this->pubnub->dataSync()->createEntity()
            ->entityId($entityId)
            ->entityClass($this->entityClass)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['name' => 'entity-' . $entityId])
            ->sync();

        return $entityId;
    }

    private function createUser(): string
    {
        $userId = 'php-sdk-event-user-' . uniqid();

        $this->pubnub->dataSync()->createUser()
            ->userId($userId)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['name' => 'user-' . $userId])
            ->sync();

        return $userId;
    }

    private function createChannel(): string
    {
        $channelId = 'php-sdk-event-channel-' . uniqid();

        $this->pubnub->dataSync()->createChannel()
            ->channelId($channelId)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['name' => 'channel-' . $channelId])
            ->sync();

        return $channelId;
    }
}
