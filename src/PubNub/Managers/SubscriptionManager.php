<?php

namespace PubNub\Managers;


use PNPresenceEventResult;
use PubNub\Builders\DTO\SubscribeOperation;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Endpoints\Presence\Server\PresenceEnvelope;
use PubNub\Endpoints\Presence\SubscribeMessage;
use PubNub\Endpoints\PubSub\Subscribe;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\Internal\PubNubSubscriptionLoopBreak;
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
                // TODO: continue if timeout
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

            print_r($result);

            if (!$this->subscriptionStatusAnnounced) {
                $pnStatus = (new PNStatus())->setCategory(PNStatusCategory::PNConnectedCategory);

                $this->listenerManager->announceStatus($pnStatus);
            }

            if (!$result->isEmpty()) {
                try {
                    foreach ($result->getMessages() as $message) {
                        $this->processIncomingPayload($message);
                    }
                } catch (PubNubSubscriptionLoopBreak $e) {
                    // TODO: announce status
                    break;
                }
            }

            $this->timetoken = (int) $result->getMetadata()->getTimetoken();
            $this->region = (int) $result->getMetadata()->getRegion();
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
     * @param array $message
     */
    protected function processMessage(array $message)
    {
        $output = null;

        return $output;
    }
}
