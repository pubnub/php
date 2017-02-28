<?php

namespace PubNub\Endpoints\Presence;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNubUtil;

class SetState extends Endpoint
{
    const PATH = "/v2/presence/sub-key/%s/channel/%s/uuid/%s/data";

    protected $subscriptionManager = null;

    /** @var array */
    protected $state = null;

    /** @var array  */
    protected $channels = [];

    /** @var array  */
    protected $groups = [];

    /**
     * @param $state
     */
    public function state($state)
    {
        $this->state = $state;
    }

    /**
     * @param string|string[] $channels
     * @return $this
     */
    public function channels($channels)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $channels);

        return $this;
    }

    /**
     * @param string[]|string $groups
     */
    public function groups($groups)
    {
        $this->groups = PubNubUtil::extendArray($this->groups, $groups);
    }

    /**
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    protected function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateChannelGroups($this->channels, $this->groups);

        if (count($this->channels) === 0 && count($this->groups) === 0) {
            throw new PubNubValidationException("State setter for channel groups is not supported yet");
        }

        if ($this->state === null || !PubNubUtil::isAssoc($this->state)) {
            throw new PubNubValidationException("State missing or not a dict");
        }
    }

    /**
     * @return null
     */
    protected function buildData()
    {
        return null;
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        // TODO SubscriptionManager
//        if ($this->subscriptionManager !== null) {
//            $this->subscriptionManager->adaptStateBuilder(StateOperation(
//                $this->channels,
//                $this->groups,
//                $this->state
//            ));
//        }

        $params = [
            'state' => PubNubUtil::writeValueAsString($this->groups)
        ];

        if (count($this->groups) > 0) {
            $params['channel-group'] = PubNubUtil::joinItems($this->groups);
        }

        return $params;
    }

    /**
     * @return string
     */
    public function buildPath()
    {
        return sprintf(
            GetState::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::joinChannels($this->channels),
            $this->pubnub->getConfiguration()->getUuid()
        );
    }

    /**
     * @return array
     */
    public function buildParams()
    {
        return parent::buildParams();
    }

    /**
     * @param array $json Decoded json
     * @return mixed
     */
    public function createResponse($json)
    {
        if (array_key_exists('state', $json) && $json['status'] === 200) {
            return new \PNSetStateResult($json['payload']);
        } else {
            return $json;
        }

    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNGetState;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    /**
     * @return string[]|string
     */
    public function getAffectedChannels()
    {
        return $this->channels;
    }

    /**
     * @return string[]|string
     */
    public function getAffectedChannelGroups()
    {
        return $this->groups;
    }

    /**
     * @return int
     */
    public function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * @return int
     */
    public function getConnectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    /**
     * @return string PNHttpMethod
     */
    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }
}