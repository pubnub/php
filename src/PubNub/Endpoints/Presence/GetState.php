<?php

namespace PubNub\Endpoints\Presence;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\PubNubUtil;

class GetState extends Endpoint
{
    const PATH = "/v2/presence/sub-key/%s/channel/%s/uuid/%s";

    /** @var array  */
    protected $channels = [];

    /** @var array  */
    protected $groups = [];

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
        $params = [];

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
        if (count($this->channels) === 1 && count($this->groups) === 0) {
            $channels = [$this->channels[0] => $json['payload']];
        } else {
            $channels = $json['payload']['channels'];
        }

        return new \PNGetStateResult($channels);
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