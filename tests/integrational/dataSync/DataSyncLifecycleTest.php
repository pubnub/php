<?php

namespace PubNubTests\integrational\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\DataSync\PNDataSyncPatch;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTests\helpers\RetriesDataSyncReads;

/**
 * Full create-read-update-delete round trip against a real keyset.
 *
 * Skipped unless DATASYNC_SUBSCRIBE_KEY, DATASYNC_PUBLISH_KEY and DATASYNC_ENTITY_CLASS are set,
 * because it needs a key whose class registry already declares the entity class it writes to. The
 * relationship half additionally needs DATASYNC_ENTITY_RELATIONSHIP, a relationship class declared
 * over two entities of DATASYNC_ENTITY_CLASS.
 * This is the test that settles the open questions no stub can answer: whether the vendor media
 * type is accepted, whether POST, PUT, PATCH and DELETE all behave as the spec describes, and
 * whether PUT bodies are covered by the PAM signature when a secret key is configured.
 */
class DataSyncLifecycleTest extends TestCase
{
    use RetriesDataSyncReads;

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
                    . 'DataSync lifecycle test against a live keyset.'
            );
        }

        $config = new PNConfiguration();
        $config->setSubscribeKey($subscribeKey);
        $config->setPublishKey($publishKey);
        $config->setUuid('datasync-lifecycle-test');
        // Ten seconds is the default and a loaded CI runner occasionally needs more than that.
        $config->setNonSubscribeRequestTimeout(30);

        $secretKey = getenv('DATASYNC_SECRET_KEY') ?: '';

        if ($secretKey !== '') {
            $config->setSecretKey($secretKey);
        }

        if ($origin = getenv('DATASYNC_ORIGIN')) {
            $config->setOrigin($origin);
        }

        $this->pubnub = new PubNub($config);
    }

    public function testEntityLifecycle(): void
    {
        $entityId = 'php-sdk-lifecycle-' . uniqid();

        $created = $this->pubnub->dataSync()->createEntity()
            ->entityId($entityId)
            ->entityClass($this->entityClass)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['make' => 'Toyota', 'model' => 'Camry'])
            ->sync();

        $this->assertSame($entityId, $created->getId());
        $this->assertNotEmpty($created->getETag());

        try {
            $fetched = $this->readEventually(
                fn() => $this->pubnub->dataSync()->getEntity()->entityId($entityId)->sync(),
                null,
                'the new entity'
            );

            $this->assertSame($entityId, $fetched->getId());
            $this->assertSame('active', $fetched->getData()->getStatus());

            $updated = $this->pubnub->dataSync()->updateEntity()
                ->entityId($entityId)
                ->ifMatchesETag($fetched->getETag())
                ->patch((new PNDataSyncPatch())->replace('/status', 'inactive'))
                ->sync();

            $this->assertSame('inactive', $updated->getData()->getStatus());

            // The ETag moved on with the patch, so replaying the stale one must be refused.
            $staleETagWasRejected = false;

            try {
                $this->pubnub->dataSync()->updateEntity()
                    ->entityId($entityId)
                    ->ifMatchesETag($fetched->getETag())
                    ->patch((new PNDataSyncPatch())->replace('/status', 'active'))
                    ->sync();
            } catch (PubNubServerException $exception) {
                $staleETagWasRejected = true;
                $this->assertSame(412, $exception->getStatusCode());
            }

            $this->assertTrue($staleETagWasRejected, 'a stale ETag must fail with 412');

            // PUT replaces the record outright, so the model field written at create time is gone.
            $replaced = $this->pubnub->dataSync()->setEntity()
                ->entityId($entityId)
                ->entityClassVersion(1)
                ->ifMatchesETag($updated->getETag())
                ->status('archived')
                ->payload(['make' => 'Honda'])
                ->sync();

            $this->assertSame('archived', $replaced->getData()->getStatus());
            $this->assertSame(['make' => 'Honda'], $replaced->getData()->getPayload());
            $this->assertNotEmpty($replaced->getETag());

            $listed = $this->pubnub->dataSync()->getEntities()
                ->entityClass($this->entityClass)
                ->limit(100)
                ->sync();

            $this->assertNotNull($listed->getPage());
        } finally {
            $deleted = $this->pubnub->dataSync()->deleteEntity()->entityId($entityId)->sync();
            $this->assertTrue($deleted->isSuccess());
        }
    }

    /**
     * Every operation the patch builder can emit, against the live server. The lifecycle tests only
     * ever replace and add, so remove, move, copy and test are otherwise proven no further than the
     * wire format.
     */
    public function testPatchSupportsEveryOperation(): void
    {
        $entityId = $this->createEntity();

        try {
            $seeded = $this->pubnub->dataSync()->setEntity()
                ->entityId($entityId)
                ->entityClassVersion(1)
                ->status('active')
                ->payload(['make' => 'Toyota', 'model' => 'Camry', 'scratch' => 'temporary'])
                ->sync();

            $patched = $this->pubnub->dataSync()->updateEntity()
                ->entityId($entityId)
                ->ifMatchesETag((string) $seeded->getETag())
                ->patch(
                    (new PNDataSyncPatch())
                        ->test('/payload/make', 'Toyota')
                        ->copy('/payload/model', '/payload/previousModel')
                        ->move('/payload/scratch', '/payload/note')
                        ->remove('/payload/model')
                        ->replace('/status', 'patched')
                )
                ->sync();

            $this->assertSame('patched', $patched->getData()->getStatus());
            $this->assertEquals(
                ['make' => 'Toyota', 'previousModel' => 'Camry', 'note' => 'temporary'],
                $patched->getData()->getPayload()
            );

            // A test operation that does not hold has to take the whole document down with it.
            $wasRejected = false;

            try {
                $this->pubnub->dataSync()->updateEntity()
                    ->entityId($entityId)
                    ->patch(
                        (new PNDataSyncPatch())
                            ->test('/payload/make', 'Honda')
                            ->replace('/status', 'never-applied')
                    )
                    ->sync();
            } catch (PubNubServerException $exception) {
                $wasRejected = true;
            }

            $this->assertTrue($wasRejected, 'a test operation that does not hold must reject the patch');

            $verified = $this->pubnub->dataSync()->getEntity()->entityId($entityId)->sync();
            $this->assertSame('patched', $verified->getData()->getStatus(), 'the rejected patch changed nothing');
        } finally {
            $this->pubnub->dataSync()->deleteEntity()->entityId($entityId)->sync();
        }
    }

    /**
     * A delete takes an ETag like the other writes do, which is the only way to be sure the record
     * being removed is the one that was read.
     */
    public function testDeleteHonoursIfMatch(): void
    {
        $entityId = $this->createEntity();
        $stale = $this->pubnub->dataSync()->getEntity()->entityId($entityId)->sync();

        try {
            $current = $this->pubnub->dataSync()->setEntity()
                ->entityId($entityId)
                ->entityClassVersion(1)
                ->status('moved-on')
                ->payload(['make' => 'Honda'])
                ->sync();

            $staleWasRejected = false;

            try {
                $this->pubnub->dataSync()->deleteEntity()
                    ->entityId($entityId)
                    ->ifMatchesETag((string) $stale->getETag())
                    ->sync();
            } catch (PubNubServerException $exception) {
                $staleWasRejected = true;
                $this->assertSame(412, $exception->getStatusCode());
            }

            $this->assertTrue($staleWasRejected, 'a delete carrying a stale ETag must fail with 412');

            $deleted = $this->pubnub->dataSync()->deleteEntity()
                ->entityId($entityId)
                ->ifMatchesETag((string) $current->getETag())
                ->sync();

            $this->assertTrue($deleted->isSuccess());
        } finally {
            try {
                $this->pubnub->dataSync()->deleteEntity()->entityId($entityId)->sync();
            } catch (PubNubServerException $exception) {
                // Already gone, which is the expected outcome.
            }
        }
    }

    public function testMissingRecordsAreReportedAsNotFound(): void
    {
        $missingId = 'php-sdk-missing-' . uniqid();

        try {
            $this->pubnub->dataSync()->getEntity()->entityId($missingId)->sync();
            $this->fail('reading a record that does not exist must fail');
        } catch (PubNubServerException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }

        try {
            $this->pubnub->dataSync()->deleteEntity()->entityId($missingId)->sync();
            $this->fail('deleting a record that does not exist must fail');
        } catch (PubNubServerException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }

    public function testCreateWithoutAnIdGetsOneFromTheServer(): void
    {
        $created = $this->pubnub->dataSync()->createEntity()
            ->entityClass($this->entityClass)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['make' => 'Toyota'])
            ->sync();

        $entityId = (string) $created->getId();

        try {
            $this->assertNotSame('', $entityId, 'the server assigns an id when none is supplied');

            $fetched = $this->readEventually(
                fn() => $this->pubnub->dataSync()->getEntity()->entityId($entityId)->sync(),
                null,
                'the server-named entity'
            );
            $this->assertSame($entityId, $fetched->getId());
        } finally {
            $this->pubnub->dataSync()->deleteEntity()->entityId($entityId)->sync();
        }
    }

    /**
     * Listing with a filter, a sort and a page size, which is the combination the signed request
     * path is most exposed to: every one of those values needs characters escaped, and the PAM
     * signature is computed over that same escaping.
     */
    public function testEntityListingFiltersSortsAndPages(): void
    {
        // A status nothing else on the shared keyset uses, so the filter selects exactly these.
        $status = 'php-sdk-filter-' . uniqid();
        $created = [];

        for ($index = 0; $index < 3; $index++) {
            $created[] = $this->createEntity($status);
        }

        try {
            // The listing index sees a new record a moment after a direct read of it works, so the
            // page is fetched until all three are in it rather than once and hopefully.
            $first = $this->readEventually(
                fn() => $this->pubnub->dataSync()->getEntities()
                    ->entityClass($this->entityClass)
                    ->filterFast("status == '$status'")
                    ->sort(['createdAt' => 'desc'])
                    ->limit(2)
                    ->sync(),
                fn($result) => count($result->getData()) === 2
                    && $result->getPage() !== null
                    && $result->getPage()->hasNext(),
                'the filtered listing'
            );

            $this->assertCount(2, $first->getData());
            $this->assertNotNull($first->getPage());
            $this->assertTrue($first->getPage()->hasNext(), 'three records over a page of two must have a next page');
            $this->assertNotEmpty($first->getPage()->getNextCursor());

            $second = $this->pubnub->dataSync()->getEntities()
                ->entityClass($this->entityClass)
                ->filterFast("status == '$status'")
                ->sort(['createdAt' => 'desc'])
                ->limit(2)
                ->cursor((string) $first->getPage()->getNextCursor())
                ->sync();

            $this->assertCount(1, $second->getData());

            $listed = array_merge($this->idsOf($first->getData()), $this->idsOf($second->getData()));

            sort($created);
            sort($listed);

            $this->assertSame($created, $listed, 'the two pages together are exactly the filtered set');
        } finally {
            foreach ($created as $entityId) {
                $this->pubnub->dataSync()->deleteEntity()->entityId($entityId)->sync();
            }
        }
    }

    /**
     * @param object[] $records
     * @return string[]
     */
    private function idsOf(array $records): array
    {
        return array_map(static fn($record) => (string) $record->getId(), $records);
    }

    /**
     * Same round trip for a relationship, which additionally proves that the two linked entities
     * and the relationship class survive a full replacement while status and payload do not.
     */
    public function testRelationshipLifecycle(): void
    {
        if ($this->relationshipClass === '') {
            $this->markTestSkipped(
                'Set DATASYNC_ENTITY_RELATIONSHIP to a relationship class registered on the keyset to run the '
                    . 'DataSync relationship lifecycle test.'
            );
        }

        $entityAId = $this->createEntity();
        $entityBId = $this->createEntity();

        try {
            $created = $this->pubnub->dataSync()->createRelationship()
                ->entityAId($entityAId)
                ->entityBId($entityBId)
                ->relationshipClass($this->relationshipClass)
                ->relationshipClassVersion(1)
                ->status('new')
                ->payload(['role' => 'member', 'joinedAt' => '2025-01-01'])
                ->sync();

            $relationshipId = (string) $created->getId();

            $this->assertNotSame('', $relationshipId);
            $this->assertNotEmpty($created->getETag());

            try {
                $fetched = $this->readEventually(
                    fn() => $this->pubnub->dataSync()->getRelationship()
                        ->relationshipId($relationshipId)
                        ->sync(),
                    null,
                    'the new relationship'
                );

                $this->assertSame($relationshipId, $fetched->getId());
                $this->assertSame('new', $fetched->getData()->getStatus());
                $this->assertSame($entityAId, $fetched->getData()->getEntityAId());
                $this->assertSame($entityBId, $fetched->getData()->getEntityBId());
                $this->assertSame($this->relationshipClass, $fetched->getData()->getRelationshipClass());

                // PUT replaces the mutable fields outright, so joinedAt does not survive it, while
                // the immutable class and entity links stay put even though PUT never sends them.
                $replaced = $this->pubnub->dataSync()->setRelationship()
                    ->relationshipId($relationshipId)
                    ->relationshipClassVersion(1)
                    ->ifMatchesETag((string) $fetched->getETag())
                    ->status('updated')
                    ->payload(['role' => 'admin'])
                    ->sync();

                $this->assertSame('updated', $replaced->getData()->getStatus());
                $this->assertSame(['role' => 'admin'], $replaced->getData()->getPayload());
                $this->assertSame($entityAId, $replaced->getData()->getEntityAId());
                $this->assertSame($entityBId, $replaced->getData()->getEntityBId());
                $this->assertSame($this->relationshipClass, $replaced->getData()->getRelationshipClass());

                $patched = $this->pubnub->dataSync()->updateRelationship()
                    ->relationshipId($relationshipId)
                    ->ifMatchesETag((string) $replaced->getETag())
                    ->patch(
                        (new PNDataSyncPatch())
                            ->replace('/status', 'patched')
                            ->add('/payload/patchedField', 'hello')
                    )
                    ->sync();

                $this->assertSame('patched', $patched->getData()->getStatus());

                $verified = $this->pubnub->dataSync()->getRelationship()
                    ->relationshipId($relationshipId)
                    ->sync();

                $this->assertSame('patched', $verified->getData()->getStatus());
                $this->assertSame(
                    ['role' => 'admin', 'patchedField' => 'hello'],
                    $verified->getData()->getPayload()
                );

                $listed = $this->pubnub->dataSync()->getRelationships()
                    ->relationshipClass($this->relationshipClass)
                    ->entityAId($entityAId)
                    ->limit(100)
                    ->sync();

                $ids = array_map(
                    static fn($relationship) => $relationship->getId(),
                    $listed->getData()
                );

                $this->assertContains($relationshipId, $ids);
            } finally {
                $deleted = $this->pubnub->dataSync()->deleteRelationship()
                    ->relationshipId($relationshipId)
                    ->sync();
                $this->assertTrue($deleted->isSuccess());
            }
        } finally {
            $this->pubnub->dataSync()->deleteEntity()->entityId($entityAId)->sync();
            $this->pubnub->dataSync()->deleteEntity()->entityId($entityBId)->sync();
        }
    }

    /**
     * Creates a throwaway entity to hang a relationship off, or to list, and returns its id.
     */
    private function createEntity(string $status = 'active'): string
    {
        $entityId = 'php-sdk-lifecycle-' . uniqid();

        $this->pubnub->dataSync()->createEntity()
            ->entityId($entityId)
            ->entityClass($this->entityClass)
            ->entityClassVersion(1)
            ->status($status)
            ->payload(['name' => 'entity-' . $entityId])
            ->sync();

        // Relationships written next link to this entity, so it has to be there first.
        $this->readableNow(
            fn() => $this->pubnub->dataSync()->getEntity()->entityId($entityId)->sync(),
            'the entity fixture'
        );

        return $entityId;
    }
}
