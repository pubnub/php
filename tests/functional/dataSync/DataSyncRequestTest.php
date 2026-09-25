<?php

namespace PubNubTests\functional\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncPatch;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use Psr\Http\Message\RequestInterface;

/**
 * The exact request each DataSync endpoint puts on the wire: verb, path, query, headers and body.
 *
 * These lock in the two things that are unique to DataSync and easy to break silently - the vendor
 * media types and the {"data": ...} request envelope.
 */
class DataSyncRequestTest extends TestCase
{
    private const ENTITY_MEDIA_TYPE = 'application/vnd.pubnub.objects.entity+json;version=1';
    private const RELATIONSHIP_MEDIA_TYPE = 'application/vnd.pubnub.objects.relationship+json;version=1';
    private const PATCH_MEDIA_TYPE = 'application/json-patch+json';

    private PubNub $pubnub;

    public function setUp(): void
    {
        parent::setUp();
        $config = new PNConfiguration();
        $config->setSubscribeKey('sub-key');
        $config->setPublishKey('pub-key');
        $config->setUuid('datasync-request-uuid');
        $this->pubnub = new PubNub($config);
    }

    /**
     * @return array<string, string>
     */
    private function query(RequestInterface $request): array
    {
        $parsed = [];
        parse_str($request->getUri()->getQuery(), $parsed);
        return $parsed;
    }

    public function testCreateEntityRequest(): void
    {
        $request = $this->pubnub->dataSync()->createEntity()
            ->entityId('vehicle-1')
            ->entityClass('vehicle')
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['make' => 'Toyota', 'model' => 'Camry'])
            ->getRequest();

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/entities', $request->getUri()->getPath());
        $this->assertSame(self::ENTITY_MEDIA_TYPE, $request->getHeaderLine('Content-Type'));
        $this->assertFalse($request->hasHeader('Accept'));

        $this->assertSame(
            '{"data":{"id":"vehicle-1","entityClass":"vehicle","entityClassVersion":1,'
                . '"status":"active","payload":{"make":"Toyota","model":"Camry"}}}',
            (string) $request->getBody()
        );
    }

    public function testCreateEntityOmitsUnsetOptionalFields(): void
    {
        $request = $this->pubnub->dataSync()->createEntity()
            ->entityClass('vehicle')
            ->entityClassVersion(1)
            ->getRequest();

        $this->assertSame(
            '{"data":{"entityClass":"vehicle","entityClassVersion":1}}',
            (string) $request->getBody()
        );
    }

    public function testCreateEntityCanDisambiguateTheClassLevel(): void
    {
        $request = $this->pubnub->dataSync()->createEntity()
            ->entityClass('vehicle')
            ->entityClassVersion(1)
            ->entityClassLevel('Global')
            ->getRequest();

        $this->assertSame(
            '{"data":{"entityClass":"vehicle","entityClassVersion":1,"entityClassLevel":"Global"}}',
            (string) $request->getBody()
        );
    }

    public function testEmptyPayloadEncodesAsJsonObject(): void
    {
        $request = $this->pubnub->dataSync()->createEntity()
            ->entityClass('vehicle')
            ->entityClassVersion(1)
            ->payload([])
            ->getRequest();

        $this->assertStringContainsString('"payload":{}', (string) $request->getBody());
    }

    public function testGetEntityRequest(): void
    {
        $request = $this->pubnub->dataSync()->getEntity()
            ->entityId('vehicle-1')
            ->getRequest();

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/entities/vehicle-1', $request->getUri()->getPath());
        $this->assertSame('', (string) $request->getBody());
    }

    public function testEntityIdIsUrlEncodedInThePath(): void
    {
        $request = $this->pubnub->dataSync()->getEntity()
            ->entityId('vehicle 1/2')
            ->getRequest();

        $this->assertSame('/v1/datasync/subkeys/sub-key/entities/vehicle%201%2F2', $request->getUri()->getPath());
    }

    public function testGetEntitiesRequest(): void
    {
        $request = $this->pubnub->dataSync()->getEntities()
            ->entityClass('vehicle')
            ->entityClassVersion(2)
            ->entityClassLevel('SubKey')
            ->limit(50)
            ->cursor('Y3Vyc29y')
            ->filterFast("status == 'active'")
            ->filter("payload.make == 'Toyota'")
            ->sort(['createdAt' => 'desc', 'status'])
            ->getRequest();

        $query = $this->query($request);

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/entities', $request->getUri()->getPath());
        $this->assertSame('vehicle', $query['entity_class']);
        $this->assertSame('2', $query['entity_class_version']);
        $this->assertSame('SubKey', $query['entity_class_level']);
        $this->assertSame('50', $query['limit']);
        $this->assertSame('Y3Vyc29y', $query['cursor']);
        $this->assertSame("status == 'active'", $query['filter_fast']);
        $this->assertSame("payload.make == 'Toyota'", $query['filter']);
        $this->assertSame('createdAt:desc,status', $query['sort']);
    }

    /**
     * A signed request has to carry its query values encoded exactly once, because the signature
     * is computed over that same single encoding. An endpoint that encodes its own values before
     * handing them over would have them signed doubly encoded, and the server would answer 403.
     */
    public function testASignedListRequestEncodesItsQueryOnlyOnce(): void
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('sub-key');
        $config->setPublishKey('pub-key');
        $config->setSecretKey('secret-key');
        $config->setUuid('datasync-request-uuid');

        $request = (new PubNub($config))->dataSync()->getEntities()
            ->entityClass('vehicle')
            ->filter("payload.make == 'Toyota'")
            ->sort(['createdAt' => 'desc'])
            ->getRequest();

        $raw = $request->getUri()->getQuery();

        $this->assertStringContainsString('sort=createdAt%3Adesc', $raw);
        $this->assertStringNotContainsString('%25', $raw, 'a %25 in the query means a value was encoded twice');

        // Recomputed the way the server does it, from what actually went on the wire.
        $params = $this->query($request);
        unset($params['signature']);

        $expected = preg_replace('/=+$/', '', 'v2.' . PubNubUtil::signSha256(
            'secret-key',
            "GET\npub-key\n" . $request->getUri()->getPath() . "\n"
                . PubNubUtil::preparePamParams($params) . "\n"
        ));

        $this->assertSame($expected, $this->query($request)['signature']);
    }

    /**
     * A direction written in capitals used to fall through to the branch that emits the field on
     * its own, which the server reads as ascending - the opposite of what was asked for.
     */
    public function testSortDirectionIsAcceptedInAnyCase(): void
    {
        $request = $this->pubnub->dataSync()->getEntities()
            ->entityClass('vehicle')
            ->sort(['createdAt' => 'DESC', 'status' => 'Asc', 'model'])
            ->getRequest();

        $this->assertSame('createdAt:desc,status:asc,model', $this->query($request)['sort']);
    }

    public function testAnUnknownSortDirectionIsRejected(): void
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage('sort direction for "createdAt" must be asc or desc');

        $this->pubnub->dataSync()->getEntities()->sort(['createdAt' => 'descending']);
    }

    public function testSetEntityRequest(): void
    {
        $request = $this->pubnub->dataSync()->setEntity()
            ->entityId('vehicle-1')
            ->entityClassVersion(1)
            ->status('inactive')
            ->payload(['make' => 'Toyota'])
            ->ifMatchesETag('abc123')
            ->getRequest();

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/entities/vehicle-1', $request->getUri()->getPath());
        $this->assertSame(self::ENTITY_MEDIA_TYPE, $request->getHeaderLine('Content-Type'));
        $this->assertSame('abc123', $request->getHeaderLine('If-Match'));

        // entityClass is immutable and must never be sent on a replace.
        $this->assertSame(
            '{"data":{"entityClassVersion":1,"status":"inactive","payload":{"make":"Toyota"}}}',
            (string) $request->getBody()
        );
    }

    public function testUpdateEntityRequest(): void
    {
        $request = $this->pubnub->dataSync()->updateEntity()
            ->entityId('vehicle-1')
            ->patch(
                (new PNDataSyncPatch())
                    ->replace('/status', 'inactive')
                    ->add('/payload/color', 'blue')
            )
            ->getRequest();

        $this->assertSame('PATCH', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/entities/vehicle-1', $request->getUri()->getPath());
        $this->assertSame(self::PATCH_MEDIA_TYPE, $request->getHeaderLine('Content-Type'));

        // The patch document is a bare array rather than a {"data": ...} envelope.
        // json_encode escapes forward slashes, so the JSON Pointers arrive as "\/status".
        $this->assertSame(
            '[{"op":"replace","path":"\/status","value":"inactive"},'
                . '{"op":"add","path":"\/payload\/color","value":"blue"}]',
            (string) $request->getBody()
        );
    }

    public function testDeleteEntityRequest(): void
    {
        $request = $this->pubnub->dataSync()->deleteEntity()
            ->entityId('vehicle-1')
            ->ifMatchesETag('abc123')
            ->getRequest();

        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/entities/vehicle-1', $request->getUri()->getPath());
        $this->assertSame('abc123', $request->getHeaderLine('If-Match'));
        $this->assertSame('', (string) $request->getBody());

        // The server answers 406 when a delete asks for a specific media type.
        $this->assertFalse($request->hasHeader('Accept'));
        $this->assertFalse($request->hasHeader('Content-Type'));
    }

    public function testCreateRelationshipRequest(): void
    {
        $request = $this->pubnub->dataSync()->createRelationship()
            ->entityAId('user-1')
            ->entityBId('vehicle-1')
            ->relationshipClass('owns')
            ->relationshipClassVersion(1)
            ->getRequest();

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/relationships', $request->getUri()->getPath());
        $this->assertSame(self::RELATIONSHIP_MEDIA_TYPE, $request->getHeaderLine('Content-Type'));
        $this->assertSame(
            '{"data":{"entityAId":"user-1","entityBId":"vehicle-1",'
                . '"relationshipClass":"owns","relationshipClassVersion":1}}',
            (string) $request->getBody()
        );
    }

    public function testCreateRelationshipSendsTheClientSuppliedIdInTheBody(): void
    {
        $request = $this->pubnub->dataSync()->createRelationship()
            ->relationshipId('rel-1')
            ->entityAId('user-1')
            ->entityBId('vehicle-1')
            ->relationshipClass('owns')
            ->relationshipClassVersion(1)
            ->status('active')
            ->payload(['role' => 'owner'])
            ->getRequest();

        $this->assertSame('/v1/datasync/subkeys/sub-key/relationships', $request->getUri()->getPath());
        $this->assertSame(
            '{"data":{"id":"rel-1","entityAId":"user-1","entityBId":"vehicle-1",'
                . '"relationshipClass":"owns","relationshipClassVersion":1,'
                . '"status":"active","payload":{"role":"owner"}}}',
            (string) $request->getBody()
        );
    }

    public function testGetRelationshipRequest(): void
    {
        $request = $this->pubnub->dataSync()->getRelationship()
            ->relationshipId('rel 1/2')
            ->getRequest();

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/relationships/rel%201%2F2', $request->getUri()->getPath());
        $this->assertSame('', (string) $request->getBody());
    }

    public function testGetRelationshipsRequest(): void
    {
        $request = $this->pubnub->dataSync()->getRelationships()
            ->relationshipClass('owns')
            ->relationshipClassVersion(2)
            ->entityAId('user-1')
            ->entityBId('vehicle-1')
            ->limit(50)
            ->cursor('Y3Vyc29y')
            ->filterFast("status == 'active'")
            ->filter("payload.role == 'owner'")
            ->sort(['createdAt' => 'desc'])
            ->getRequest();

        $query = $this->query($request);

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/relationships', $request->getUri()->getPath());
        $this->assertSame('owns', $query['relationship_class']);
        $this->assertSame('2', $query['relationship_class_version']);
        $this->assertSame('user-1', $query['entity_a_id']);
        $this->assertSame('vehicle-1', $query['entity_b_id']);
        $this->assertSame('50', $query['limit']);
        $this->assertSame('Y3Vyc29y', $query['cursor']);
        $this->assertSame("status == 'active'", $query['filter_fast']);
        $this->assertSame("payload.role == 'owner'", $query['filter']);
        $this->assertSame('createdAt:desc', $query['sort']);
    }

    public function testSetRelationshipRequest(): void
    {
        $request = $this->pubnub->dataSync()->setRelationship()
            ->relationshipId('rel-1')
            ->relationshipClassVersion(1)
            ->status('updated')
            ->payload(['role' => 'admin'])
            ->ifMatchesETag('abc123')
            ->getRequest();

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/relationships/rel-1', $request->getUri()->getPath());
        $this->assertSame(self::RELATIONSHIP_MEDIA_TYPE, $request->getHeaderLine('Content-Type'));
        $this->assertSame('abc123', $request->getHeaderLine('If-Match'));

        // The class and the two linked entities are immutable, so a replace never sends them.
        $this->assertSame(
            '{"data":{"relationshipClassVersion":1,"status":"updated","payload":{"role":"admin"}}}',
            (string) $request->getBody()
        );
    }

    public function testUpdateRelationshipRequest(): void
    {
        $request = $this->pubnub->dataSync()->updateRelationship()
            ->relationshipId('rel-1')
            ->ifMatchesETag('abc123')
            ->patch(
                (new PNDataSyncPatch())
                    ->replace('/status', 'patched')
                    ->add('/payload/patchedField', 'hello')
            )
            ->getRequest();

        $this->assertSame('PATCH', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/relationships/rel-1', $request->getUri()->getPath());
        $this->assertSame(self::PATCH_MEDIA_TYPE, $request->getHeaderLine('Content-Type'));
        $this->assertSame('abc123', $request->getHeaderLine('If-Match'));
        $this->assertSame(
            '[{"op":"replace","path":"\/status","value":"patched"},'
                . '{"op":"add","path":"\/payload\/patchedField","value":"hello"}]',
            (string) $request->getBody()
        );
    }

    public function testDeleteRelationshipRequest(): void
    {
        $request = $this->pubnub->dataSync()->deleteRelationship()
            ->relationshipId('rel-1')
            ->ifMatchesETag('abc123')
            ->getRequest();

        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/v1/datasync/subkeys/sub-key/relationships/rel-1', $request->getUri()->getPath());
        $this->assertSame('abc123', $request->getHeaderLine('If-Match'));
        $this->assertSame('', (string) $request->getBody());
        $this->assertFalse($request->hasHeader('Accept'));
        $this->assertFalse($request->hasHeader('Content-Type'));
    }

    public function testCreateUserRequest(): void
    {
        $request = $this->pubnub->dataSync()->createUser()
            ->userId('user-1')
            ->entityClassVersion(1)
            ->payload(['name' => 'Alice'])
            ->getRequest();

        $this->assertSame('/v1/datasync/subkeys/sub-key/users', $request->getUri()->getPath());
        $this->assertSame(
            'application/vnd.pubnub.objects.user+json;version=1',
            $request->getHeaderLine('Content-Type')
        );

        // entityClass is left out so the server applies the predefined "User" class.
        $this->assertSame(
            '{"data":{"id":"user-1","entityClassVersion":1,"payload":{"name":"Alice"}}}',
            (string) $request->getBody()
        );
    }

    public function testGetUsersOmitsEntityClassWhenUnset(): void
    {
        $request = $this->pubnub->dataSync()->getUsers()->limit(10)->getRequest();

        $query = $this->query($request);

        $this->assertSame('/v1/datasync/subkeys/sub-key/users', $request->getUri()->getPath());
        $this->assertArrayNotHasKey('entity_class', $query);
        $this->assertSame('10', $query['limit']);
    }

    public function testGetUsersSendsEntityClassWhenNarrowedToASubclass(): void
    {
        $request = $this->pubnub->dataSync()->getUsers()
            ->entityClass('Employee')
            ->entityClassVersion(2)
            ->getRequest();

        $query = $this->query($request);

        $this->assertSame('Employee', $query['entity_class']);
        $this->assertSame('2', $query['entity_class_version']);
    }

    public function testGetChannelsSendsEntityClassWhenNarrowedToASubclass(): void
    {
        $request = $this->pubnub->dataSync()->getChannels()
            ->entityClass('PrivateChannel')
            ->getRequest();

        $this->assertSame('PrivateChannel', $this->query($request)['entity_class']);
    }

    public function testCreateChannelRequest(): void
    {
        $request = $this->pubnub->dataSync()->createChannel()
            ->channelId('channel-1')
            ->entityClassVersion(1)
            ->getRequest();

        $this->assertSame('/v1/datasync/subkeys/sub-key/channels', $request->getUri()->getPath());
        $this->assertSame(
            'application/vnd.pubnub.objects.channel+json;version=1',
            $request->getHeaderLine('Content-Type')
        );
    }

    public function testCreateMembershipRequest(): void
    {
        $request = $this->pubnub->dataSync()->createMembership()
            ->channelId('channel-1')
            ->userId('user-1')
            ->relationshipClassVersion(1)
            ->getRequest();

        $this->assertSame('/v1/datasync/subkeys/sub-key/memberships', $request->getUri()->getPath());
        $this->assertSame(
            'application/vnd.pubnub.objects.membership+json;version=1',
            $request->getHeaderLine('Content-Type')
        );

        // Membership is a predefined relationship class, so no relationshipClass is sent.
        $this->assertSame(
            '{"data":{"channelId":"channel-1","userId":"user-1","relationshipClassVersion":1}}',
            (string) $request->getBody()
        );
    }

    public function testGetMembershipsFiltersByUserAndChannel(): void
    {
        $request = $this->pubnub->dataSync()->getMemberships()
            ->userId('user-1')
            ->channelId('channel-1')
            ->relationshipClassVersion(3)
            ->getRequest();

        $query = $this->query($request);

        $this->assertSame('user-1', $query['user_id']);
        $this->assertSame('channel-1', $query['channel_id']);
        $this->assertSame('3', $query['relationship_class_version']);
        $this->assertArrayNotHasKey('relationship_class', $query);
    }
}
