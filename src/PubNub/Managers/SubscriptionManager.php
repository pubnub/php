<?php

namespace PubNub\Managers;


use PNPresenceEventResult;
use PubNub\Builders\DTO\SubscribeOperation;
use PubNub\Builders\DTO\UnsubscribeOperation;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Endpoints\Presence\Leave;
use PubNub\Endpoints\Presence\Server\PresenceEnvelope;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\Models\Server\SubscribeMessage;
use PubNub\Endpoints\PubSub\Subscribe;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubConnectionException;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\Consumer\PubSub\SubscribeEnvelope;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class SubscriptionManager
{
    /** @var  PubNub */
    protected $pubnub;

    /** @var  ListenerManager */
    protected $listenerManager;

    /** @var  int */
    protected $timetoken;

    /** @var  string */
    protected $region;

    /** @var  bool */
    protected $subscriptionStatusAnnounced;

    /**
     * SubscriptionManager constructor.
     * @param PubNub $pubnub
     */
    public function __construct(PubNub $pubnub)
    {
        $this->pubnub = $pubnub;
        $this->listenerManager = new ListenerManager($pubnub);
        $this->subscriptionState = new StateManager($pubnub);
        $this->subscriptionStatusAnnounced = false;
    }

    public function start()
    {
        while (True) {
            $combinedChannels = $this->subscriptionState->prepareChannelList(true);
            $combinedChannelGroups = $this->subscriptionState->prepareChannelGroupList(true);

            if (empty($combinedChannels) && empty($combinedChannelGroups)) {
                return;
            }

            try {
                /** @var SubscribeEnvelope $result */
                $result = (new Subscribe($this->pubnub))
                    ->channels($combinedChannels)
                    ->groups($combinedChannelGroups)
                    ->setTimetoken($this->timetoken)
                    ->setRegion($this->region)
                    ->setFilterExpression($this->pubnub->getConfiguration()->getFilterExpression())
                    ->sync();
            } catch (PubNubConnectionException $e) {
                if ($e->getStatus()->getCategory() === PNStatusCategory::PNTimeoutCategory) {
                    continue;
                }
                print_r($e->getMessage());
                // TODO: announce status
                return;
            } catch (PubNubServerException $e) {
                print_r($e->getMessage());
                // TODO: announce status
                return;
            } catch (\Exception $e) {
                print_r($e->getMessage());
                // TODO: announce status
                return;
            }
            // TODO Handle connect event

            if (!$this->subscriptionStatusAnnounced) {
                $pnStatus = (new PNStatus())->setCategory(PNStatusCategory::PNConnectedCategory);

                try {
                    $this->listenerManager->announceStatus($pnStatus);
                } catch (PubNubUnsubscribeException $e) {
                    $this->adaptUnsubscribeBuilder($e->getUnsubscribeOperation($this), false);
                    break;
                }

                $this->subscriptionStatusAnnounced = true;
            }

            if (!$result->isEmpty()) {
                try {
                    foreach ($result->getMessages() as $message) {
                        $this->processIncomingPayload($message);
                    }
                } catch (PubNubUnsubscribeException $e) {
                    $this->adaptUnsubscribeBuilder($e->getUnsubscribeOperation($this));
                    break;
                }
            }

            $this->timetoken = (int) $result->getMetadata()->getTimetoken();
            $this->region = (int) $result->getMetadata()->getRegion();
        }
    }

    /**
     * @param UnsubscribeOperation $operation
     * @param bool $announceStatus
     */
    public function adaptUnsubscribeBuilder(UnsubscribeOperation $operation, $announceStatus = true)
    {
        $leave = (new Leave($this->pubnub))
            ->channels($operation->getChannels())
            ->groups($operation->getChannelGroups());

        $this->subscriptionState->adaptUnsubscribeBuilder($operation);

        $this->subscriptionStatusAnnounced = false;

        $leave->sync();

        if ($announceStatus) {
            $pnStatus = (new PNStatus())->setCategory(PNStatusCategory::PNDisconnectedCategory);

            $this->listenerManager->announceStatus($pnStatus);
        }
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function addListener($listener)
    {
        $this->listenerManager->addListener($listener);
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function removeListener($listener)
    {
        $this->listenerManager->removeListener($listener);
    }

    public function getSubscribedGroups()
    {
        return $this->subscriptionState->prepareChannelList(false);
    }

    public function getSubscribedChannelGroups()
    {
        return $this->subscriptionState->prepareChannelGroupList(false);
    }

    /**
     * @param SubscribeOperation $subscribeOperation
     */
    public function adaptSubscribeBuilder($subscribeOperation)
    {
        $this->subscriptionState->adaptSubscribeBuilder($subscribeOperation);

        if ($subscribeOperation->getTimetoken() !== null) {
            $this->timetoken = $subscribeOperation->getTimetoken();
        }
    }

    /**
     * @param SubscribeMessage $message
     * @throws PubNubUnsubscribeException
     */
    protected function processIncomingPayload($message)
    {
        $channel = $message->getChannel();
        $subscriptionMatch = $message->getSubscriptionMatch();
        $publishMetadata = $message->getPublishMetaData();

        if ($channel !== null && $channel === $subscriptionMatch) {
            $subscriptionMatch = null;
        }

        if (PubNubUtil::stringEndsWith($channel, '-pnpres')) {
            $presencePayload = PresenceEnvelope::fromJson($message->getPayload());

            $strippedPresenceChannel = null;
            $strippedPresenceSubscription = null;

            if ($channel !== null) {
                $strippedPresenceChannel = str_replace("-pnpres", "", $channel);
            }

            if ($subscriptionMatch !== null) {
                $strippedPresenceSubscription = str_replace("-pnpres", "", $subscriptionMatch);
            }

            $pnPresenceResult = new PNPresenceEventResult(
                $presencePayload->getAction(),
                $strippedPresenceChannel,
                $strippedPresenceSubscription,
                $publishMetadata->getPublishTimetoken(),
                $presencePayload->getOccupancy(),
                $presencePayload->getUuid(),
                $presencePayload->getTimestamp(),
                $presencePayload->getData()
            );

            $this->listenerManager->announcePresence($pnPresenceResult);
        } else {
            $extractedMessage = $this->processMessage($message->getPayload());
            $publisher = $message->getIssuingClientId();

            if ($extractedMessage === null) {
                // TODO implement logger
            }

            $pnMessageResult = new PNMessageResult(
                $extractedMessage,
                $channel,
                $subscriptionMatch,
                $publishMetadata->getPublishTimetoken(),
                $message->getPublishMetaData(),
                $publisher
            );

            $this->listenerManager->announceMessage($pnMessageResult);
        }
    }

    /**
     * @param mixed $message
     * @return mixed
     */
    protected function processMessage($message)
    {
        $this->pubnub->getConfiguration()->setCipherKey(null);

        if ($this->pubnub->getConfiguration()->getCipherKey() === null) {
            return $message;
        } else {
            return $this->pubnub->getConfiguration()->getCryptoSafe()->decrypt($message);
        }
    }
}
