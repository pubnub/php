<?php

namespace PubNubTests\unit\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Models\Consumer\DataSync\PNDataSyncEventResult;

/**
 * Parsing of the DataSync change notifications delivered over the subscribe stream.
 */
class DataSyncEventResultTest extends TestCase
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'version' => '3.0',
            'metadata' => [
                'event' => 'create',
                'source' => 'data-sync',
                'type' => 'entity',
                'className' => 'vehicle',
                'classVersion' => '1',
                'classLevel' => 'SubKey',
            ],
            'data' => [
                'id' => 'vehicle-1',
                'status' => 'active',
                'payload' => ['make' => 'Toyota'],
                'createdAt' => '2026-08-21T10:00:00Z',
                'eTag' => 'abc123',
            ],
        ], $overrides);
    }

    public function testParsesEntityCreateEvent(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload(), 'vehicle-1', null, '17000000000000000');

        $this->assertNotNull($event);
        $this->assertSame('3.0', $event->getVersion());
        $this->assertSame('data-sync', $event->getSource());
        $this->assertSame('create', $event->getEvent());
        $this->assertSame('entity', $event->getType());
        $this->assertSame('vehicle-1', $event->getId());
        $this->assertSame('vehicle-1', $event->getChannel());
        $this->assertSame('17000000000000000', $event->getTimetoken());

        $entity = $event->getEntity();
        $this->assertNotNull($entity);
        $this->assertSame('active', $entity->getStatus());
        $this->assertSame(['make' => 'Toyota'], $entity->getPayload());
        $this->assertSame('abc123', $entity->getETag());
    }

    public function testSurfacesTheClassNameUnchanged(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload([
            'metadata' => [
                'event' => 'create',
                'source' => 'data-sync',
                'type' => 'entity',
                'className' => 'fleet:vehicle',
                'classVersion' => '1',
            ],
        ]));

        $this->assertNotNull($event);
        $this->assertSame('fleet:vehicle', $event->getClassName());
        $this->assertSame('fleet:vehicle', $event->getEntity()->getEntityClass());
    }

    public function testReadsTheClassLevel(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload());

        $this->assertNotNull($event);
        $this->assertSame('SubKey', $event->getClassLevel());
    }

    /**
     * The class, its version and its level all travel in the metadata rather than in the record,
     * so all three have to be put back onto the record the event carries - otherwise an entity
     * taken out of an event looks like it has no class level at all.
     */
    public function testTheRecordCarriesTheClassLevelTheMetadataNamed(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload());

        $this->assertNotNull($event);
        $this->assertNotNull($event->getEntity());
        $this->assertSame('SubKey', $event->getEntity()->getEntityClassLevel());
        $this->assertSame('vehicle', $event->getEntity()->getEntityClass());
        $this->assertSame(1, $event->getEntity()->getEntityClassVersion());
    }

    public function testMatchesEventAndTypeRegardlessOfCasing(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload([
            'metadata' => [
                'event' => 'CREATE',
                'source' => 'data-sync',
                'type' => 'Entity',
                'className' => 'vehicle',
                'classVersion' => '1',
            ],
        ]));

        $this->assertNotNull($event);
        $this->assertSame('CREATE', $event->getEvent(), 'the raw casing is preserved');
        $this->assertNotNull($event->getEntity());
    }

    public function testCastsClassVersionToInt(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload());

        $this->assertNotNull($event);
        $this->assertSame(1, $event->getClassVersion());
        $this->assertSame(1, $event->getEntity()->getEntityClassVersion());
    }

    public function testDeleteEventCarriesOnlyIdAndDeletedAt(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload([
            'metadata' => [
                'event' => 'delete',
                'source' => 'data-sync',
                'type' => 'entity',
                'className' => 'vehicle',
                'classVersion' => '1',
            ],
            'data' => [
                'id' => 'vehicle-1',
                'deletedAt' => '2026-08-21T11:00:00Z',
            ],
        ]));

        $this->assertNotNull($event);
        $this->assertSame('delete', $event->getEvent());
        $this->assertSame('vehicle-1', $event->getId());
        $this->assertSame('2026-08-21T11:00:00Z', $event->getDeletedAt());
        $this->assertNull($event->getEntity());
        $this->assertNull($event->getRelationship());
    }

    public function testParsesRelationshipEvent(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload([
            'metadata' => [
                'event' => 'update',
                'source' => 'data-sync',
                'type' => 'relationship',
                'className' => 'owns',
                'classVersion' => '2',
            ],
            'data' => [
                'id' => 'rel-1',
                'entityAId' => 'user-1',
                'entityBId' => 'vehicle-1',
            ],
        ]));

        $this->assertNotNull($event);
        $this->assertNull($event->getEntity());

        $relationship = $event->getRelationship();
        $this->assertNotNull($relationship);
        $this->assertSame('user-1', $relationship->getEntityAId());
        $this->assertSame('vehicle-1', $relationship->getEntityBId());
        $this->assertSame('owns', $relationship->getRelationshipClass());
        $this->assertSame(2, $relationship->getRelationshipClassVersion());
        $this->assertNull($event->getMembership());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function predefinedEntityTypeProvider(): array
    {
        return ['user' => ['user'], 'channel' => ['channel']];
    }

    /**
     * The predefined User and Channel classes are entities, so they arrive shaped like one.
     *
     * @dataProvider predefinedEntityTypeProvider
     */
    public function testParsesPredefinedEntityEvent(string $type): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload([
            'metadata' => [
                'event' => 'create',
                'source' => 'data-sync',
                'type' => $type,
                'className' => ucfirst($type),
                'classVersion' => '1',
                'classLevel' => 'Global',
            ],
            'data' => [
                'id' => $type . '-1',
                'status' => 'active',
                'payload' => ['name' => 'Alice'],
            ],
        ]));

        $this->assertNotNull($event);
        $this->assertSame($type, $event->getType());
        $this->assertSame('Global', $event->getClassLevel());
        $this->assertNull($event->getRelationship());

        $entity = $event->getEntity();
        $this->assertNotNull($entity);
        $this->assertSame($type . '-1', $entity->getId());
        $this->assertSame(ucfirst($type), $entity->getEntityClass());
        $this->assertSame(['name' => 'Alice'], $entity->getPayload());
    }

    public function testParsesMembershipEventAsBothARelationshipAndAMembership(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload([
            'metadata' => [
                'event' => 'update',
                'source' => 'data-sync',
                'type' => 'membership',
                'className' => 'Membership',
                'classVersion' => '1',
                'classLevel' => 'Global',
            ],
            'data' => [
                'id' => 'mem-1',
                'channelId' => 'channel-1',
                'userId' => 'user-1',
                'status' => 'active',
                'payload' => ['role' => 'member'],
            ],
        ]));

        $this->assertNotNull($event);
        $this->assertSame('membership', $event->getType());
        $this->assertNull($event->getEntity());

        $membership = $event->getMembership();
        $this->assertNotNull($membership);
        $this->assertSame('mem-1', $membership->getId());
        $this->assertSame('channel-1', $membership->getChannelId());
        $this->assertSame('user-1', $membership->getUserId());
        $this->assertSame('Membership', $membership->getRelationshipClass());
        $this->assertSame(1, $membership->getRelationshipClassVersion());
        $this->assertSame(['role' => 'member'], $membership->getPayload());

        // The same record also arrives as a relationship, with the channel first and the user second.
        $relationship = $event->getRelationship();
        $this->assertNotNull($relationship);
        $this->assertSame('mem-1', $relationship->getId());
        $this->assertSame('channel-1', $relationship->getEntityAId());
        $this->assertSame('user-1', $relationship->getEntityBId());
    }

    public function testDeleteEventOfAPredefinedTypeCarriesNoRecord(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload([
            'metadata' => [
                'event' => 'delete',
                'source' => 'data-sync',
                'type' => 'membership',
                'className' => 'Membership',
                'classVersion' => '1',
            ],
            'data' => [
                'id' => 'mem-1',
                'deletedAt' => '2026-08-21T11:00:00Z',
            ],
        ]));

        $this->assertNotNull($event);
        $this->assertSame('mem-1', $event->getId());
        $this->assertSame('2026-08-21T11:00:00Z', $event->getDeletedAt());
        $this->assertNull($event->getMembership());
        $this->assertNull($event->getRelationship());
        $this->assertNull($event->getEntity());
    }

    public function testUnknownTypeCarriesNoRecord(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload([
            'metadata' => [
                'event' => 'create',
                'source' => 'data-sync',
                'type' => 'something-new',
                'className' => 'vehicle',
                'classVersion' => '1',
            ],
        ]));

        $this->assertNotNull($event);
        $this->assertSame('something-new', $event->getType());
        $this->assertNull($event->getEntity());
        $this->assertNull($event->getRelationship());
        $this->assertNull($event->getMembership());
    }

    public function testIgnoresPayloadsFromOtherSources(): void
    {
        $payload = $this->payload();
        $payload['metadata']['source'] = 'something-else';

        $this->assertNull(PNDataSyncEventResult::fromPayload($payload));
    }

    public function testIgnoresPayloadsWithoutMetadata(): void
    {
        $this->assertNull(PNDataSyncEventResult::fromPayload(['data' => ['id' => 'x']]));
        $this->assertNull(PNDataSyncEventResult::fromPayload('plain string message'));
    }
}
