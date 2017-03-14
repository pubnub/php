<?php

namespace PubNub\Endpoints\Presence;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNubUtil;

class Leave extends Endpoint
{
    const PATH = "/v2/presence/sub-key/%s/channel/%s/leave";

    /** @var array */
    protected $channels = [];

    /** @var array */
    protected $groups = [];

    /**
     * @param $channels string|string[]
     * @return $this
     */
    public function channels($channels)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $channels);

        return $this;
    }

    /**
     * @param $groups string|string[]
     * @return $this
     */
    public function groups($groups)
    {
        $this->groups = PubNubUtil::extendArray($this->groups, $groups);

        return $this;
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

    public function getAffectedChannels()
    {
        return parent::getAffectedChannels();
    }

    public function getAffectedChannelGroups()
    {
        return parent::getAffectedChannelGroups();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Leave";
    }

    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (count($this->channels) === 0 && count($this->groups) === 0) {
            throw new PubNubValidationException("Channel or group missing");
        }
    }

    /**
     * @param array $json Decoded json
     * @return array
     */
    protected function createResponse($json)
    {
        return $json;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNUnsubscribeOperation;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    /**
     * @return null|string
     */
    protected function buildData()
    {
        return null;
    }

    /**
     * @return string
     */
    protected function buildPath()
    {
        return sprintf(
            Leave::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::joinChannels($this->channels));
    }

    /**
     * @return $params
     */
    protected function customParams()
    {
        $params = [];

        if (count($this->groups) > 0) {
            $params['channel-group'] = PubNubUtil::joinItems($this->groups);
        }

        return $params;
    }

    /**
     * @return int
     */
    protected function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * @return int
     */
    protected function getConnectTimeout()
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
