<?php

namespace PubNub\Managers;


use PubNub\Builders\DTO\SubscribeOperation;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Endpoints\PubSub\Subscribe;
use PubNub\Exceptions\PubNubConnectionException;
use PubNub\Exceptions\PubNubException;
use PubNub\Exceptions\PubNubServerException;
use PubNub\PubNub;

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

    /**
     * SubscriptionManager constructor.
     * @param PubNub $pubnub
     */
    public function __construct(PubNub $pubnub)
    {
        $this->pubnub = $pubnub;
        $this->listenerManager = new ListenerManager($pubnub);
        $this->subscriptionState = new StateManager($pubnub);
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
                $result = (new Subscribe($this->pubnub))
                    ->setChannels($combinedChannels)
                    ->setChannelGroups($combinedChannelGroups)
                    ->setTimetoken($this->timetoken)
                    ->setRegion($this->region)
                    ->setFilterExpression($this->pubnub->getConfiguration()->getFilterExpression())
                    ->sync();
            } catch (PubNubConnectionException $e) {
//                if ($e->get)
            } catch (PubNubServerException $e) {
            } catch (\Exception $e) {
            }

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
}
