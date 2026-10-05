<?php

namespace PubNubTests\unit\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Endpoints\Endpoint;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncPatch;
use PubNub\PNConfiguration;
use PubNub\PubNub;

/**
 * Validation rules of the DataSync endpoints, exercised without touching the network.
 */
class DataSyncValidationTest extends TestCase
{
    private PubNub $pubnub;

    public function setUp(): void
    {
        parent::setUp();
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setPublishKey('demo');
        $config->setUuid('datasync-validation-uuid');
        $this->pubnub = new PubNub($config);
    }

    private function validate(Endpoint $endpoint): void
    {
        $method = new \ReflectionMethod($endpoint, 'validateParams');
        $method->invoke($endpoint);
    }

    public function testCreateEntityRequiresEntityClass(): void
    {
        $endpoint = $this->pubnub->dataSync()->createEntity()->entityClassVersion(1);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('entityClass missing');
        $this->validate($endpoint);
    }

    public function testCreateEntityRejectsClassVersionBelowOne(): void
    {
        $endpoint = $this->pubnub->dataSync()->createEntity()
            ->entityClass('vehicle')
            ->entityClassVersion(0);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('entityClassVersion must be greater than or equal to 1');
        $this->validate($endpoint);
    }

    public function testCreateEntityWithoutIdIsValid(): void
    {
        $endpoint = $this->pubnub->dataSync()->createEntity()
            ->entityClass('vehicle')
            ->entityClassVersion(1);

        $this->validate($endpoint);
        $this->assertTrue(true, 'the server generates an id when none is supplied');
    }

    public function testGetEntityRequiresId(): void
    {
        $endpoint = $this->pubnub->dataSync()->getEntity();

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('entityId missing');
        $this->validate($endpoint);
    }

    public function testGetEntitiesRequiresEntityClass(): void
    {
        $endpoint = $this->pubnub->dataSync()->getEntities();

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('entityClass missing');
        $this->validate($endpoint);
    }

    public function testSetEntityRequiresId(): void
    {
        $endpoint = $this->pubnub->dataSync()->setEntity()->entityClassVersion(1);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('entityId missing');
        $this->validate($endpoint);
    }

    public function testSetEntityRequiresClassVersion(): void
    {
        $endpoint = $this->pubnub->dataSync()->setEntity()->entityId('e-1');

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('entityClassVersion must be greater than or equal to 1');
        $this->validate($endpoint);
    }

    public function testUpdateEntityRequiresId(): void
    {
        $endpoint = $this->pubnub->dataSync()->updateEntity()
            ->patch((new PNDataSyncPatch())->replace('/status', 'inactive'));

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('entityId missing');
        $this->validate($endpoint);
    }

    public function testUpdateEntityRequiresAtLeastOneOperation(): void
    {
        $endpoint = $this->pubnub->dataSync()->updateEntity()
            ->entityId('e-1')
            ->patch(new PNDataSyncPatch());

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('patch operations missing');
        $this->validate($endpoint);
    }

    public function testUpdateEntityRejectsReplaceWithoutValue(): void
    {
        $endpoint = $this->pubnub->dataSync()->updateEntity()
            ->entityId('e-1')
            ->patch([['op' => 'replace', 'path' => '/status']]);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('patch operation "replace" requires a value');
        $this->validate($endpoint);
    }

    public function testUpdateEntityRejectsMoveWithoutFrom(): void
    {
        $endpoint = $this->pubnub->dataSync()->updateEntity()
            ->entityId('e-1')
            ->patch([['op' => 'move', 'path' => '/payload/b']]);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('patch operation "move" requires a from path');
        $this->validate($endpoint);
    }

    public function testUpdateEntityAcceptsRemoveWithoutValue(): void
    {
        $endpoint = $this->pubnub->dataSync()->updateEntity()
            ->entityId('e-1')
            ->patch((new PNDataSyncPatch())->remove('/payload/obsolete'));

        $this->validate($endpoint);
        $this->assertTrue(true, 'remove carries neither value nor from');
    }

    public function testDeleteEntityRequiresId(): void
    {
        $endpoint = $this->pubnub->dataSync()->deleteEntity();

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('entityId missing');
        $this->validate($endpoint);
    }

    public function testCreateRelationshipRequiresBothEntityIds(): void
    {
        $endpoint = $this->pubnub->dataSync()->createRelationship()
            ->entityBId('b')
            ->relationshipClass('owns')
            ->relationshipClassVersion(1);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('entityAId missing');
        $this->validate($endpoint);
    }

    public function testCreateRelationshipRequiresRelationshipClass(): void
    {
        $endpoint = $this->pubnub->dataSync()->createRelationship()
            ->entityAId('a')
            ->entityBId('b')
            ->relationshipClassVersion(1);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('relationshipClass missing');
        $this->validate($endpoint);
    }

    public function testCreateRelationshipRejectsClassVersionBelowOne(): void
    {
        $endpoint = $this->pubnub->dataSync()->createRelationship()
            ->entityAId('a')
            ->entityBId('b')
            ->relationshipClass('owns')
            ->relationshipClassVersion(0);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('relationshipClassVersion must be greater than or equal to 1');
        $this->validate($endpoint);
    }

    public function testCreateRelationshipWithoutIdIsValid(): void
    {
        $endpoint = $this->pubnub->dataSync()->createRelationship()
            ->entityAId('a')
            ->entityBId('b')
            ->relationshipClass('owns')
            ->relationshipClassVersion(1);

        $this->validate($endpoint);
        $this->assertTrue(true, 'the server generates an id when none is supplied');
    }

    public function testGetRelationshipRequiresId(): void
    {
        $endpoint = $this->pubnub->dataSync()->getRelationship();

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('relationshipId missing');
        $this->validate($endpoint);
    }

    public function testGetRelationshipsRequiresRelationshipClass(): void
    {
        $endpoint = $this->pubnub->dataSync()->getRelationships();

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('relationshipClass missing');
        $this->validate($endpoint);
    }

    public function testGetRelationshipsWithOnlyTheClassIsValid(): void
    {
        $endpoint = $this->pubnub->dataSync()->getRelationships()->relationshipClass('owns');

        $this->validate($endpoint);
        $this->assertTrue(true, 'entityAId and entityBId only narrow an otherwise valid listing');
    }

    public function testSetRelationshipRequiresId(): void
    {
        $endpoint = $this->pubnub->dataSync()->setRelationship()->relationshipClassVersion(1);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('relationshipId missing');
        $this->validate($endpoint);
    }

    public function testSetRelationshipRequiresClassVersion(): void
    {
        $endpoint = $this->pubnub->dataSync()->setRelationship()->relationshipId('rel-1');

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('relationshipClassVersion must be greater than or equal to 1');
        $this->validate($endpoint);
    }

    public function testUpdateRelationshipRequiresId(): void
    {
        $endpoint = $this->pubnub->dataSync()->updateRelationship()
            ->patch((new PNDataSyncPatch())->replace('/status', 'patched'));

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('relationshipId missing');
        $this->validate($endpoint);
    }

    public function testUpdateRelationshipRequiresAtLeastOneOperation(): void
    {
        $endpoint = $this->pubnub->dataSync()->updateRelationship()
            ->relationshipId('rel-1')
            ->patch(new PNDataSyncPatch());

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('patch operations missing');
        $this->validate($endpoint);
    }

    public function testDeleteRelationshipRequiresId(): void
    {
        $endpoint = $this->pubnub->dataSync()->deleteRelationship();

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('relationshipId missing');
        $this->validate($endpoint);
    }

    public function testCreateMembershipRequiresChannelId(): void
    {
        $endpoint = $this->pubnub->dataSync()->createMembership()
            ->userId('u-1')
            ->relationshipClassVersion(1);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('channelId missing');
        $this->validate($endpoint);
    }

    public function testCreateMembershipRequiresUserId(): void
    {
        $endpoint = $this->pubnub->dataSync()->createMembership()
            ->channelId('c-1')
            ->relationshipClassVersion(1);

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('userId missing');
        $this->validate($endpoint);
    }

    public function testGetMembershipsNeedsNoFilters(): void
    {
        $endpoint = $this->pubnub->dataSync()->getMemberships();

        $this->validate($endpoint);
        $this->assertTrue(true, 'listing every membership of the key is a valid request');
    }

    public function testCreateUserDoesNotRequireEntityClass(): void
    {
        $endpoint = $this->pubnub->dataSync()->createUser()->entityClassVersion(1);

        $this->validate($endpoint);
        $this->assertTrue(true, 'entityClass defaults to "User" server-side');
    }

    public function testDeleteChannelRequiresId(): void
    {
        $endpoint = $this->pubnub->dataSync()->deleteChannel();

        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('channelId missing');
        $this->validate($endpoint);
    }
}
