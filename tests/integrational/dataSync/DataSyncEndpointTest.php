<?php

namespace PubNubTests\integrational\dataSync;

use PubNubTestCase;
use PubNub\Models\Consumer\DataSync\PNDataSyncDeleteResult;
use PubNub\Models\Consumer\DataSync\PNDataSyncEntitiesResult;
use PubNub\Models\Consumer\DataSync\PNDataSyncEntityResult;
use PubNub\Models\Consumer\DataSync\PNDataSyncMembershipResult;
use PubNub\Models\Consumer\DataSync\PNDataSyncPatch;
use PubNub\Models\Consumer\DataSync\PNDataSyncRelationshipResult;
use PubNub\Models\Consumer\DataSync\PNDataSyncRelationshipsResult;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

/**
 * DataSync operations driven through the public sync() API against a stubbed PSR-18 client.
 *
 * Two of these exist specifically to prove the shared Endpoint fixes DataSync depends on: creates
 * answer 201 rather than 200, and deletes answer 200 with no body at all.
 */
class DataSyncEndpointTest extends PubNubTestCase
{
    private const UUID = 'sampleUUID';

    private PsrStubClient $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = new PsrStubClient();
        $this->pubnub_demo->setClient($this->client);
        $this->pubnub_demo->getConfiguration()->setUuid(self::UUID);
    }

    /**
     * @param array<string, string> $extraQuery
     */
    private function stub(string $path, array $extraQuery = []): PsrStub
    {
        return $this->client->stubFor($path)->withQuery(array_merge([
            'pnsdk' => $this->pubnub_demo->getSdkFullName(),
            'uuid' => self::UUID,
        ], $extraQuery));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function json(array $data): string
    {
        return (string) json_encode($data);
    }

    public function testCreateEntityAcceptsHttp201(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/entities')
            ->setResponseStatus(201)
            ->setResponseBody($this->json([
                'data' => [
                    'id' => 'vehicle-1',
                    'entityClass' => 'vehicle',
                    'entityClassVersion' => 1,
                    'status' => 'active',
                    'payload' => ['make' => 'Toyota'],
                    'createdAt' => '2026-08-21T10:00:00Z',
                    'eTag' => 'abc123',
                ],
            ]));

        $result = $this->pubnub_demo->dataSync()->createEntity()
            ->entityId('vehicle-1')
            ->entityClass('vehicle')
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['make' => 'Toyota'])
            ->sync();

        $this->assertInstanceOf(PNDataSyncEntityResult::class, $result);
        $this->assertSame('vehicle-1', $result->getId());
        $this->assertSame('abc123', $result->getETag());
        $this->assertSame('vehicle', $result->getData()->getEntityClass());
        $this->assertSame(1, $result->getData()->getEntityClassVersion());
        $this->assertSame(['make' => 'Toyota'], $result->getData()->getPayload());
    }

    public function testDeleteEntityAcceptsEmptyBody(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/entities/vehicle-1')->setResponseBody('');

        $result = $this->pubnub_demo->dataSync()->deleteEntity()
            ->entityId('vehicle-1')
            ->sync();

        $this->assertInstanceOf(PNDataSyncDeleteResult::class, $result);
        $this->assertTrue($result->isSuccess());
    }

    /**
     * Only a delete is allowed to answer with nothing. Everything else owes a body, so an empty one
     * is a broken response and has to be reported rather than turned into an empty record.
     */
    public function testAnEmptyBodyOnAReadIsAnError(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/entities/vehicle-1')->setResponseBody('');

        $envelope = $this->pubnub_demo->dataSync()->getEntity()->entityId('vehicle-1')->envelope();

        $this->assertTrue($envelope->isError());
        $this->assertNull($envelope->getResult());
    }

    public function testGetEntity(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/entities/vehicle-1')
            ->setResponseBody($this->json([
                'data' => [
                    'id' => 'vehicle-1',
                    'entityClass' => 'vehicle',
                    'entityClassVersion' => 1,
                    'payload' => ['make' => 'Toyota', 'model' => 'Camry'],
                ],
            ]));

        $result = $this->pubnub_demo->dataSync()->getEntity()->entityId('vehicle-1')->sync();

        $this->assertSame('vehicle-1', $result->getId());
        $this->assertSame(['make' => 'Toyota', 'model' => 'Camry'], $result->getData()->getPayload());
    }

    public function testGetEntitiesParsesPagination(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/entities', ['entity_class' => 'vehicle', 'limit' => '2'])
            ->setResponseBody($this->json([
                'data' => [
                    ['id' => 'vehicle-1', 'entityClass' => 'vehicle'],
                    ['id' => 'vehicle-2', 'entityClass' => 'vehicle'],
                ],
                'meta' => ['next_cursor' => 'Y3Vyc29y', 'has_next' => true, 'limit' => 2],
            ]));

        $result = $this->pubnub_demo->dataSync()->getEntities()
            ->entityClass('vehicle')
            ->limit(2)
            ->sync();

        $this->assertInstanceOf(PNDataSyncEntitiesResult::class, $result);
        $this->assertSame(2, $result->count());
        $this->assertSame('vehicle-1', $result->getData()[0]->getId());
        $this->assertSame('Y3Vyc29y', $result->getPage()->getNextCursor());
        $this->assertTrue($result->getPage()->hasNext());
        $this->assertSame(2, $result->getPage()->getLimit());
    }

    public function testGetEntitiesOnLastPage(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/entities', ['entity_class' => 'vehicle'])
            ->setResponseBody($this->json([
                'data' => [],
                'meta' => ['has_next' => false],
            ]));

        $result = $this->pubnub_demo->dataSync()->getEntities()->entityClass('vehicle')->sync();

        $this->assertSame(0, $result->count());
        $this->assertFalse($result->getPage()->hasNext());
        $this->assertNull($result->getPage()->getNextCursor());
    }

    public function testSetEntity(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/entities/vehicle-1')
            ->setResponseBody($this->json([
                'data' => [
                    'id' => 'vehicle-1',
                    'status' => 'inactive',
                    'eTag' => 'def456',
                ],
            ]));

        $result = $this->pubnub_demo->dataSync()->setEntity()
            ->entityId('vehicle-1')
            ->entityClassVersion(1)
            ->status('inactive')
            ->ifMatchesETag('abc123')
            ->sync();

        $this->assertSame('inactive', $result->getData()->getStatus());
        $this->assertSame('def456', $result->getETag());
    }

    public function testUpdateEntity(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/entities/vehicle-1')
            ->setResponseBody($this->json([
                'data' => ['id' => 'vehicle-1', 'payload' => ['color' => 'blue']],
            ]));

        $result = $this->pubnub_demo->dataSync()->updateEntity()
            ->entityId('vehicle-1')
            ->patch((new PNDataSyncPatch())->add('/payload/color', 'blue'))
            ->sync();

        $this->assertSame(['color' => 'blue'], $result->getData()->getPayload());
    }

    public function testCreateRelationship(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/relationships')
            ->setResponseStatus(201)
            ->setResponseBody($this->json([
                'data' => [
                    'id' => 'rel-1',
                    'entityAId' => 'user-1',
                    'entityBId' => 'vehicle-1',
                    'relationshipClass' => 'owns',
                    'relationshipClassVersion' => 1,
                ],
            ]));

        $result = $this->pubnub_demo->dataSync()->createRelationship()
            ->entityAId('user-1')
            ->entityBId('vehicle-1')
            ->relationshipClass('owns')
            ->relationshipClassVersion(1)
            ->sync();

        $this->assertInstanceOf(PNDataSyncRelationshipResult::class, $result);
        $this->assertSame('user-1', $result->getData()->getEntityAId());
        $this->assertSame('vehicle-1', $result->getData()->getEntityBId());
    }

    public function testGetRelationship(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/relationships/rel-1')
            ->setResponseBody($this->json([
                'data' => [
                    'id' => 'rel-1',
                    'entityAId' => 'user-1',
                    'entityBId' => 'vehicle-1',
                    'relationshipClass' => 'owns',
                    'relationshipClassVersion' => 1,
                    'status' => 'active',
                    'payload' => ['role' => 'owner'],
                    'createdAt' => '2026-08-21T10:00:00Z',
                    'eTag' => 'abc123',
                ],
            ]));

        $result = $this->pubnub_demo->dataSync()->getRelationship()->relationshipId('rel-1')->sync();

        $this->assertSame('rel-1', $result->getId());
        $this->assertSame('abc123', $result->getETag());
        $this->assertSame('owns', $result->getData()->getRelationshipClass());
        $this->assertSame(1, $result->getData()->getRelationshipClassVersion());
        $this->assertSame('active', $result->getData()->getStatus());
        $this->assertSame(['role' => 'owner'], $result->getData()->getPayload());
        $this->assertSame('2026-08-21T10:00:00Z', $result->getData()->getCreatedAt());
    }

    public function testGetRelationshipsParsesPagination(): void
    {
        $this->stub(
            '/v1/datasync/subkeys/demo/relationships',
            ['relationship_class' => 'owns', 'entity_a_id' => 'user-1', 'limit' => '2']
        )->setResponseBody($this->json([
            'data' => [
                ['id' => 'rel-1', 'entityAId' => 'user-1', 'entityBId' => 'vehicle-1'],
                ['id' => 'rel-2', 'entityAId' => 'user-1', 'entityBId' => 'vehicle-2'],
            ],
            'meta' => ['next_cursor' => 'Y3Vyc29y', 'has_next' => true, 'limit' => 2],
        ]));

        $result = $this->pubnub_demo->dataSync()->getRelationships()
            ->relationshipClass('owns')
            ->entityAId('user-1')
            ->limit(2)
            ->sync();

        $this->assertInstanceOf(PNDataSyncRelationshipsResult::class, $result);
        $this->assertSame(2, $result->count());
        $this->assertSame('rel-1', $result->getData()[0]->getId());
        $this->assertSame('vehicle-2', $result->getData()[1]->getEntityBId());
        $this->assertSame('Y3Vyc29y', $result->getPage()->getNextCursor());
        $this->assertTrue($result->getPage()->hasNext());
    }

    public function testSetRelationship(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/relationships/rel-1')
            ->setResponseBody($this->json([
                'data' => [
                    'id' => 'rel-1',
                    'entityAId' => 'user-1',
                    'entityBId' => 'vehicle-1',
                    'relationshipClass' => 'owns',
                    'relationshipClassVersion' => 1,
                    'status' => 'updated',
                    'payload' => ['role' => 'admin'],
                    'eTag' => 'def456',
                ],
            ]));

        $result = $this->pubnub_demo->dataSync()->setRelationship()
            ->relationshipId('rel-1')
            ->relationshipClassVersion(1)
            ->status('updated')
            ->payload(['role' => 'admin'])
            ->ifMatchesETag('abc123')
            ->sync();

        $this->assertSame('updated', $result->getData()->getStatus());
        $this->assertSame(['role' => 'admin'], $result->getData()->getPayload());
        $this->assertSame('def456', $result->getETag());
    }

    public function testUpdateRelationship(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/relationships/rel-1')
            ->setResponseBody($this->json([
                'data' => [
                    'id' => 'rel-1',
                    'status' => 'patched',
                    'payload' => ['role' => 'admin', 'patchedField' => 'hello'],
                ],
            ]));

        $result = $this->pubnub_demo->dataSync()->updateRelationship()
            ->relationshipId('rel-1')
            ->patch(
                (new PNDataSyncPatch())
                    ->replace('/status', 'patched')
                    ->add('/payload/patchedField', 'hello')
            )
            ->sync();

        $this->assertSame('patched', $result->getData()->getStatus());
        $this->assertSame(['role' => 'admin', 'patchedField' => 'hello'], $result->getData()->getPayload());
    }

    public function testDeleteRelationshipAcceptsEmptyBody(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/relationships/rel-1')->setResponseBody('');

        $result = $this->pubnub_demo->dataSync()->deleteRelationship()->relationshipId('rel-1')->sync();

        $this->assertInstanceOf(PNDataSyncDeleteResult::class, $result);
        $this->assertTrue($result->isSuccess());
    }

    public function testCreateMembership(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/memberships')
            ->setResponseStatus(201)
            ->setResponseBody($this->json([
                'data' => [
                    'id' => 'mem-1',
                    'channelId' => 'channel-1',
                    'userId' => 'user-1',
                    'relationshipClass' => 'Membership',
                    'relationshipClassVersion' => 1,
                ],
            ]));

        $result = $this->pubnub_demo->dataSync()->createMembership()
            ->channelId('channel-1')
            ->userId('user-1')
            ->relationshipClassVersion(1)
            ->sync();

        $this->assertInstanceOf(PNDataSyncMembershipResult::class, $result);
        $this->assertSame('channel-1', $result->getData()->getChannelId());
        $this->assertSame('user-1', $result->getData()->getUserId());
        $this->assertSame('Membership', $result->getData()->getRelationshipClass());
        $this->assertSame(1, $result->getData()->getRelationshipClassVersion());
    }

    public function testGetUsersFiltersByEntityClass(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/users', ['entity_class' => 'Employee'])
            ->setResponseBody($this->json([
                'data' => [['id' => 'user-1', 'entityClass' => 'Employee']],
                'meta' => ['has_next' => false],
            ]));

        $result = $this->pubnub_demo->dataSync()->getUsers()->entityClass('Employee')->sync();

        $this->assertSame(1, $result->count());
        $this->assertSame('Employee', $result->getData()[0]->getEntityClass());
    }

    public function testDeleteMembershipAcceptsEmptyBody(): void
    {
        $this->stub('/v1/datasync/subkeys/demo/memberships/mem-1')->setResponseBody('');

        $result = $this->pubnub_demo->dataSync()->deleteMembership()->membershipId('mem-1')->sync();

        $this->assertTrue($result->isSuccess());
    }
}
