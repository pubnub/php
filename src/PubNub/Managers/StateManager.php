<?php

namespace PubNub\Managers;


use PubNub\Builders\DTO\SubscribeOperation;
use PubNub\Models\SubscriptionItem;
use PubNub\PubNub;

class StateManager
{
    /** @var  PubNub */
    protected $pubnub;

    /** @var array  */
    protected $channels = [];
    protected $presenceChannels = [];
    protected $channelGroups = [];
    protected $presenceChannelGroups = [];

    /**
     * ListenerManager constructor.
     * @param PubNub $pubnub
     */
    public function __construct(PubNub $pubnub)
    {
        $this->pubnub = $pubnub;
    }

    /**
     * @param SubscribeOperation $subscribeOperation
     */
    public function adaptSubscribeBuilder($subscribeOperation)
    {
        foreach ($subscribeOperation->getChannels() as $channel) {
            $subscriptionItem = (new SubscriptionItem())->setName($channel);
            $this->channels[$channel] = $subscriptionItem;

            if ($subscribeOperation->isPresenceEnabled()) {
                $presenceSubscriptionItem = (new SubscriptionItem())->setName($channel);
                $this->presenceChannels[$channel] = $presenceSubscriptionItem;
            }
        }

        foreach ($subscribeOperation->getChannelGroups() as $channelGroup) {
            $subscriptionItem = (new SubscriptionItem())->setName($channelGroup);
            $this->channelGroups[$channelGroup] = $subscriptionItem;

            if ($subscribeOperation->isPresenceEnabled()) {
                $subscriptionItem = (new SubscriptionItem())->setName($channelGroup);
                $this->presenceChannelGroups[$channelGroup] = $subscriptionItem;
            }
        }
    }

    /**
     * @param bool $includePresence
     * @return string[]
     */
    public function prepareChannelList($includePresence)
    {
        return $this->prepareMembershipList(
            $this->channels,
            $this->presenceChannels,
            $includePresence
        );
    }

    /**
     * @param bool $includePresence
     * @return string[]
     */
    public function prepareChannelGroupList($includePresence)
    {
        return $this->prepareMembershipList(
            $this->channelGroups,
            $this->presenceChannelGroups,
            $includePresence
        );
    }

    /**
     * @param array $dataStorage
     * @param array $presenceStorage
     * @param bool $includePresence
     *
     * @return string[]
     */
    private function prepareMembershipList($dataStorage, $presenceStorage, $includePresence)
    {
        $response = [];

        /**
         * @var  string $key
         * @var  SubscriptionItem $channelGroupItem
         */
        foreach ($dataStorage as $key => $channelGroupItem) {
            $response[] = $channelGroupItem->getName();
        }

        if ($includePresence) {

            /**
             * @var  string $key
             * @var  SubscriptionItem $presenceChannelGroupItem
             */
            foreach ($presenceStorage as $key => $presenceChannelGroupItem) {
                $response[] = $presenceChannelGroupItem->getName() . "-pnpres";
            }
        }

        return $response;
    }

}
