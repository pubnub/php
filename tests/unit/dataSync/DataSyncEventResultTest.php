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
                'className' => 'Base:vehicle',
                'classVersion' => '1',
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

    public function testKeepsOnlyTheMostDerivedClassName(): void
    {
        $event = PNDataSyncEventResult::fromPayload($this->payload());

        $this->assertNotNull($event);
        $this->assertSame('vehicle', $event->getClassName());
        $this->assertSame('vehicle', $event->getEntity()->getEntityClass());
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
