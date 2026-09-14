<?php

namespace PubNubTests\integrational\dataSync;

use PubNub\Callbacks\SubscribeCallback;
use PubNub\Models\Consumer\DataSync\PNDataSyncEventResult;
use PubNubTestCase;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

/**
 * Dispatch of the subscribe stream's message type 5 against a stubbed transport.
 *
 * The live event tests cover the same path end to end, but only these run without a keyset, and
 * only these can prove the negative: a type 5 message that is not a DataSync event has to fall
 * through to the regular message callback rather than being swallowed.
 */
class DataSyncSubscribeTest extends PubNubTestCase
{
    private const HANDSHAKE = '{"t":{"t":"14818963579052943","r":12},"m":[]}';
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
     * Answers the handshake, then hands out one message and lets the loop end on the third
     * request, for which no stub exists.
     */
    private function stubSubscribe(string $message): void
    {
        $this->client->addStub((new PsrStub('/v2/subscribe/demo/test/0'))
            ->withQuery([
                'pnsdk' => $this->encodedSdkName,
                'uuid' => self::UUID,
            ])
            ->setResponseBody(self::HANDSHAKE));

        $this->client->addStub((new PsrStub('/v2/subscribe/demo/test/0'))
            ->withQuery([
                'tt' => '14818963579052943',
                'tr' => '12',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => self::UUID,
            ])
            ->setResponseBody('{"t":{"t":"14921661962885137","r":12},"m":[' . $message . ']}'));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function envelope(array $payload, int $messageType = 5): string
    {
        return (string) json_encode([
            'a' => '1',
            'f' => 0,
            'e' => $messageType,
            'i' => 'publisher-uuid',
            'p' => ['t' => '14921661962867845', 'r' => 12],
            'k' => 'demo',
            'c' => 'test',
            'u' => [],
            'd' => $payload,
            'b' => 'test',
        ]);
    }

    public function testDataSyncEventReachesTheListener(): void
    {
        $this->stubSubscribe($this->envelope([
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
            ],
        ]));

        $callback = new DataSyncSubscribeCallback();
        $this->pubnub_demo->addListener($callback);
        $this->pubnub_demo->subscribe()->channel('test')->execute();

        $this->assertCount(1, $callback->dataSyncEvents);
        $this->assertCount(0, $callback->messages);

        $event = $callback->dataSyncEvents[0];
        $this->assertSame('create', $event->getEvent());
        $this->assertSame('entity', $event->getType());
        $this->assertSame('SubKey', $event->getClassLevel());
        $this->assertSame('test', $event->getChannel());
        $this->assertSame('14921661962867845', $event->getTimetoken());
        $this->assertNotNull($event->getEntity());
        $this->assertSame('vehicle-1', $event->getEntity()->getId());
        $this->assertSame(['make' => 'Toyota'], $event->getEntity()->getPayload());
    }

    public function testMembershipEventReachesTheListener(): void
    {
        $this->stubSubscribe($this->envelope([
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
            ],
        ]));

        $callback = new DataSyncSubscribeCallback();
        $this->pubnub_demo->addListener($callback);
        $this->pubnub_demo->subscribe()->channel('test')->execute();

        $this->assertCount(1, $callback->dataSyncEvents);

        $membership = $callback->dataSyncEvents[0]->getMembership();
        $this->assertNotNull($membership);
        $this->assertSame('channel-1', $membership->getChannelId());
        $this->assertSame('user-1', $membership->getUserId());
    }

    public function testUnrelatedMessageTypeFiveFallsThroughToMessage(): void
    {
        $this->stubSubscribe($this->envelope(['metadata' => ['source' => 'something-else']]));

        $callback = new DataSyncSubscribeCallback();
        $this->pubnub_demo->addListener($callback);
        $this->pubnub_demo->subscribe()->channel('test')->execute();

        $this->assertCount(0, $callback->dataSyncEvents);
        $this->assertCount(1, $callback->messages);
    }

    public function testRegularMessageIsUnaffected(): void
    {
        $this->stubSubscribe($this->envelope(['text' => 'hey'], 0));

        $callback = new DataSyncSubscribeCallback();
        $this->pubnub_demo->addListener($callback);
        $this->pubnub_demo->subscribe()->channel('test')->execute();

        $this->assertCount(0, $callback->dataSyncEvents);
        $this->assertCount(1, $callback->messages);
        $this->assertSame(['text' => 'hey'], $callback->messages[0]);
    }
}

//phpcs:ignore PSR1.Classes.ClassDeclaration
class DataSyncSubscribeCallback extends SubscribeCallback
{
    /** @var PNDataSyncEventResult[] */
    public array $dataSyncEvents = [];

    /** @var mixed[] */
    public array $messages = [];

    public function status($pubnub, $status): void
    {
    }

    /**
     * @param \PubNub\PubNub $pubnub
     * @param \PubNub\Models\Consumer\PubSub\PNMessageResult $message
     */
    public function message($pubnub, $message): void
    {
        $this->messages[] = $message->getMessage();
    }

    /**
     * @param \PubNub\PubNub $pubnub
     * @param mixed $presence
     */
    public function presence($pubnub, $presence): void
    {
    }

    public function dataSyncEvent($pubnub, $event): void
    {
        $this->dataSyncEvents[] = $event;
    }
}
