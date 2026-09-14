<?php

namespace PubNubTests\helpers;

use PubNub\Callbacks\SubscribeCallback;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\Models\Consumer\DataSync\PNDataSyncEventResult;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PubNub;

/**
 * Collects DataSync events off a live subscribe loop.
 *
 * PHP subscribes on the calling thread, so the writes that trigger the events have to be made from
 * inside the connect callback. That leaves the loop with nothing to break out on if an event never
 * arrives, so a sentinel message is bounced off the same channel once a second: it gives the loop a
 * regular hook to check progress on and, after a fixed number of rounds, to give up on.
 */
class DataSyncEventCollector extends SubscribeCallback
{
    public const SENTINEL = '__datasync-test-sentinel__';

    /** @var PNDataSyncEventResult[] */
    private array $events = [];

    private string $channel;

    private int $expected;

    /** @var callable */
    private $trigger;

    /** @var callable|null */
    private $accepts;

    private int $roundsLeft;

    /**
     * @param string $channel Channel the events and the sentinel travel on.
     * @param int $expected Number of events to collect before unsubscribing.
     * @param callable $trigger Writes to perform once the subscribe connection is up.
     * @param callable|null $accepts Keeps only the events this returns true for. A record written
     *     shortly before subscribing can still have its event land inside the window, so a test
     *     that shares a channel with another record needs to say which events are its own.
     * @param int $graceRounds Roughly how many seconds to keep waiting after the writes.
     */
    public function __construct(
        string $channel,
        int $expected,
        callable $trigger,
        ?callable $accepts = null,
        int $graceRounds = 15
    ) {
        $this->channel = $channel;
        $this->expected = $expected;
        $this->trigger = $trigger;
        $this->accepts = $accepts;
        $this->roundsLeft = $graceRounds;
    }

    /**
     * @return PNDataSyncEventResult[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param PubNub $pubnub
     * @param PNStatus $status
     * @return void
     */
    public function status($pubnub, $status): void
    {
        if ($status->getCategory() !== PNStatusCategory::PNConnectedCategory) {
            return;
        }

        ($this->trigger)();
        $this->pump($pubnub);
    }

    /**
     * @param PubNub $pubnub
     * @param PNMessageResult $message
     * @return void
     */
    public function message($pubnub, $message): void
    {
        $payload = $message->getMessage();

        if (is_array($payload) && ($payload['sentinel'] ?? null) === self::SENTINEL) {
            $this->pump($pubnub);
        }
    }

    /**
     * @param PubNub $pubnub
     * @param mixed $presence
     * @return void
     */
    public function presence($pubnub, $presence): void
    {
    }

    /**
     * @param PubNub $pubnub
     * @param PNDataSyncEventResult $event
     * @return void
     */
    public function dataSyncEvent($pubnub, $event): void
    {
        if ($this->accepts !== null && !($this->accepts)($event)) {
            return;
        }

        $this->events[] = $event;

        if (count($this->events) >= $this->expected) {
            throw new PubNubUnsubscribeException();
        }
    }

    /**
     * @param PubNub $pubnub
     * @throws PubNubUnsubscribeException once the events are in or the grace period is spent.
     */
    private function pump($pubnub): void
    {
        if (count($this->events) >= $this->expected || $this->roundsLeft <= 0) {
            throw new PubNubUnsubscribeException();
        }

        $this->roundsLeft--;
        sleep(1);

        $pubnub->publish()
            ->channel($this->channel)
            ->message(['sentinel' => self::SENTINEL])
            ->sync();
    }
}
