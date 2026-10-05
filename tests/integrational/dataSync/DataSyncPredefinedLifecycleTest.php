<?php

namespace PubNubTests\integrational\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Models\Consumer\DataSync\PNDataSyncPatch;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTests\helpers\RetriesDataSyncReads;

/**
 * Full create-read-update-delete round trip for the three predefined families, against a real
 * keyset.
 *
 * Unlike the generic entity and relationship tests there is no class to configure: User, Channel
 * and Membership are built into every key, which is why these endpoints leave the class name out
 * of the request and send a vendor media type of their own. That is the part worth proving live -
 * that POST, GET, PUT, PATCH and DELETE behave the same way here as they do for the generic
 * families, and that the server fills in the predefined class itself.
 *
 * Skipped unless DATASYNC_SUBSCRIBE_KEY and DATASYNC_PUBLISH_KEY are set.
 */
class DataSyncPredefinedLifecycleTest extends TestCase
{
    use RetriesDataSyncReads;

    private PubNub $pubnub;

    public function setUp(): void
    {
        parent::setUp();

        $subscribeKey = getenv('DATASYNC_SUBSCRIBE_KEY') ?: '';
        $publishKey = getenv('DATASYNC_PUBLISH_KEY') ?: '';

        if ($subscribeKey === '' || $publishKey === '') {
            $this->markTestSkipped(
                'Set DATASYNC_SUBSCRIBE_KEY and DATASYNC_PUBLISH_KEY to run the DataSync predefined '
                    . 'lifecycle test against a live keyset.'
            );
        }

        $config = new PNConfiguration();
        $config->setSubscribeKey($subscribeKey);
        $config->setPublishKey($publishKey);
        $config->setUuid('datasync-predefined-lifecycle-test');
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

    public function testUserLifecycle(): void
    {
        $userId = 'php-sdk-user-' . uniqid();

        $created = $this->pubnub->dataSync()->createUser()
            ->userId($userId)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['name' => 'Alice'])
            ->sync();

        $this->assertSame($userId, $created->getId());
        $this->assertNotEmpty($created->getETag());

        try {
            $fetched = $this->readEventually(
                fn() => $this->pubnub->dataSync()->getUser()->userId($userId)->sync(),
                null,
                'the new user'
            );

            $this->assertSame($userId, $fetched->getId());
            $this->assertSame('active', $fetched->getData()->getStatus());
            $this->assertSame(['name' => 'Alice'], $fetched->getData()->getPayload());

            // The class was never sent, so whatever comes back is the server's own.
            $this->assertStringContainsStringIgnoringCase(
                'user',
                (string) $fetched->getData()->getEntityClass()
            );

            $patched = $this->pubnub->dataSync()->updateUser()
                ->userId($userId)
                ->ifMatchesETag((string) $fetched->getETag())
                ->patch(
                    (new PNDataSyncPatch())
                        ->replace('/status', 'inactive')
                        ->add('/payload/nickname', 'Ali')
                )
                ->sync();

            $this->assertSame('inactive', $patched->getData()->getStatus());
            // Key order is the server's business, so compare by content.
            $this->assertEquals(['name' => 'Alice', 'nickname' => 'Ali'], $patched->getData()->getPayload());

            // PUT replaces the record outright, so the patched field is gone again.
            $replaced = $this->pubnub->dataSync()->setUser()
                ->userId($userId)
                ->entityClassVersion(1)
                ->ifMatchesETag((string) $patched->getETag())
                ->status('archived')
                ->payload(['name' => 'Alice Cooper'])
                ->sync();

            $this->assertSame('archived', $replaced->getData()->getStatus());
            $this->assertSame(['name' => 'Alice Cooper'], $replaced->getData()->getPayload());

            $this->assertUserIsListed($userId);
        } finally {
            $deleted = $this->pubnub->dataSync()->deleteUser()->userId($userId)->sync();
            $this->assertTrue($deleted->isSuccess());
        }
    }

    public function testChannelLifecycle(): void
    {
        $channelId = 'php-sdk-channel-' . uniqid();

        $created = $this->pubnub->dataSync()->createChannel()
            ->channelId($channelId)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['name' => 'Support'])
            ->sync();

        $this->assertSame($channelId, $created->getId());
        $this->assertNotEmpty($created->getETag());

        try {
            $fetched = $this->readEventually(
                fn() => $this->pubnub->dataSync()->getChannel()->channelId($channelId)->sync(),
                null,
                'the new channel'
            );

            $this->assertSame($channelId, $fetched->getId());
            $this->assertSame('active', $fetched->getData()->getStatus());
            $this->assertSame(['name' => 'Support'], $fetched->getData()->getPayload());
            $this->assertStringContainsStringIgnoringCase(
                'channel',
                (string) $fetched->getData()->getEntityClass()
            );

            $patched = $this->pubnub->dataSync()->updateChannel()
                ->channelId($channelId)
                ->ifMatchesETag((string) $fetched->getETag())
                ->patch(
                    (new PNDataSyncPatch())
                        ->replace('/status', 'muted')
                        ->add('/payload/topic', 'billing')
                )
                ->sync();

            $this->assertSame('muted', $patched->getData()->getStatus());
            $this->assertEquals(['name' => 'Support', 'topic' => 'billing'], $patched->getData()->getPayload());

            $replaced = $this->pubnub->dataSync()->setChannel()
                ->channelId($channelId)
                ->entityClassVersion(1)
                ->ifMatchesETag((string) $patched->getETag())
                ->status('archived')
                ->payload(['name' => 'Support (retired)'])
                ->sync();

            $this->assertSame('archived', $replaced->getData()->getStatus());
            $this->assertSame(['name' => 'Support (retired)'], $replaced->getData()->getPayload());

            $this->assertChannelIsListed($channelId);
        } finally {
            $deleted = $this->pubnub->dataSync()->deleteChannel()->channelId($channelId)->sync();
            $this->assertTrue($deleted->isSuccess());
        }
    }

    /**
     * A membership is the predefined relationship between a channel and a user, so the same round
     * trip also has to show that the two links survive a replacement that never sends them, and
     * that the record can be listed from either side.
     */
    public function testMembershipLifecycle(): void
    {
        $channelId = $this->createChannel();
        $userId = $this->createUser();
        $membershipId = 'php-sdk-mem-' . uniqid();

        try {
            $created = $this->pubnub->dataSync()->createMembership()
                ->membershipId($membershipId)
                ->channelId($channelId)
                ->userId($userId)
                ->relationshipClassVersion(1)
                ->status('active')
                ->payload(['role' => 'member', 'joinedAt' => '2025-01-01'])
                ->sync();

            $this->assertSame($membershipId, $created->getId());
            $this->assertNotEmpty($created->getETag());

            try {
                $fetched = $this->readEventually(
                    fn() => $this->pubnub->dataSync()->getMembership()
                        ->membershipId($membershipId)
                        ->sync(),
                    null,
                    'the new membership'
                );

                $this->assertSame($membershipId, $fetched->getId());
                $this->assertSame($channelId, $fetched->getData()->getChannelId());
                $this->assertSame($userId, $fetched->getData()->getUserId());
                $this->assertSame('active', $fetched->getData()->getStatus());
                $this->assertStringContainsStringIgnoringCase(
                    'membership',
                    (string) $fetched->getData()->getRelationshipClass()
                );

                // PUT carries neither the class nor the two links, which must survive it anyway.
                $replaced = $this->pubnub->dataSync()->setMembership()
                    ->membershipId($membershipId)
                    ->relationshipClassVersion(1)
                    ->ifMatchesETag((string) $fetched->getETag())
                    ->status('updated')
                    ->payload(['role' => 'admin'])
                    ->sync();

                $this->assertSame('updated', $replaced->getData()->getStatus());
                $this->assertSame(['role' => 'admin'], $replaced->getData()->getPayload());
                $this->assertSame($channelId, $replaced->getData()->getChannelId());
                $this->assertSame($userId, $replaced->getData()->getUserId());

                $patched = $this->pubnub->dataSync()->updateMembership()
                    ->membershipId($membershipId)
                    ->ifMatchesETag((string) $replaced->getETag())
                    ->patch(
                        (new PNDataSyncPatch())
                            ->replace('/status', 'patched')
                            ->add('/payload/note', 'promoted')
                    )
                    ->sync();

                $this->assertSame('patched', $patched->getData()->getStatus());
                $this->assertEquals(['role' => 'admin', 'note' => 'promoted'], $patched->getData()->getPayload());

                $this->assertMembershipIsListed(
                    $membershipId,
                    ['userId' => $userId],
                    'the membership should be listed among the channels the user belongs to'
                );
                $this->assertMembershipIsListed(
                    $membershipId,
                    ['channelId' => $channelId],
                    'and among the members of the channel'
                );
            } finally {
                $deleted = $this->pubnub->dataSync()->deleteMembership()
                    ->membershipId($membershipId)
                    ->sync();
                $this->assertTrue($deleted->isSuccess());
            }
        } finally {
            $this->pubnub->dataSync()->deleteUser()->userId($userId)->sync();
            $this->pubnub->dataSync()->deleteChannel()->channelId($channelId)->sync();
        }
    }

    /**
     * Newest first, so the record written moments ago is on the first page however many the keyset
     * has accumulated. Retried as well, because the listing index picks a record up a moment after
     * a direct read of it already works.
     */
    private function assertUserIsListed(string $userId): void
    {
        $listed = $this->readEventually(
            fn() => $this->pubnub->dataSync()->getUsers()
                ->limit(100)
                ->sort(['createdAt' => 'desc'])
                ->sync(),
            fn($result) => in_array($userId, $this->idsOf($result->getData()), true),
            'the user listing'
        );

        $this->assertNotNull($listed->getPage());
        $this->assertContains($userId, $this->idsOf($listed->getData()));
    }

    private function assertChannelIsListed(string $channelId): void
    {
        $listed = $this->readEventually(
            fn() => $this->pubnub->dataSync()->getChannels()
                ->limit(100)
                ->sort(['createdAt' => 'desc'])
                ->sync(),
            fn($result) => in_array($channelId, $this->idsOf($result->getData()), true),
            'the channel listing'
        );

        $this->assertNotNull($listed->getPage());
        $this->assertContains($channelId, $this->idsOf($listed->getData()));
    }

    /**
     * @param array{userId?: string, channelId?: string} $side
     */
    private function assertMembershipIsListed(string $membershipId, array $side, string $message): void
    {
        $listed = $this->readEventually(
            function () use ($side) {
                $endpoint = $this->pubnub->dataSync()->getMemberships()->limit(100);

                if (isset($side['userId'])) {
                    $endpoint->userId($side['userId']);
                }

                if (isset($side['channelId'])) {
                    $endpoint->channelId($side['channelId']);
                }

                return $endpoint->sync();
            },
            fn($result) => in_array($membershipId, $this->idsOf($result->getData()), true),
            'the membership listing'
        );

        $this->assertContains($membershipId, $this->idsOf($listed->getData()), $message);
    }

    /**
     * @param object[] $records
     * @return string[]
     */
    private function idsOf(array $records): array
    {
        return array_map(static fn($record) => (string) $record->getId(), $records);
    }

    private function createUser(): string
    {
        $userId = 'php-sdk-user-' . uniqid();

        $this->pubnub->dataSync()->createUser()
            ->userId($userId)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['name' => 'user-' . $userId])
            ->sync();

        // The membership written next links to this user, so it has to be there first.
        $this->readableNow(
            fn() => $this->pubnub->dataSync()->getUser()->userId($userId)->sync(),
            'the user fixture'
        );

        return $userId;
    }

    private function createChannel(): string
    {
        $channelId = 'php-sdk-channel-' . uniqid();

        $this->pubnub->dataSync()->createChannel()
            ->channelId($channelId)
            ->entityClassVersion(1)
            ->status('active')
            ->payload(['name' => 'channel-' . $channelId])
            ->sync();

        $this->readableNow(
            fn() => $this->pubnub->dataSync()->getChannel()->channelId($channelId)->sync(),
            'the channel fixture'
        );

        return $channelId;
    }
}
