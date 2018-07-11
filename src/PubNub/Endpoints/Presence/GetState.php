<?php

namespace PubNub\Endpoints\Presence;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Presence\PNGetStateResult;
use PubNub\PubNubUtil;


class GetState extends Endpoint
{
    const PATH = "/v2/presence/sub-key/%s/channel/%s/uuid/%s";

    /** @var array  */
    protected $channels = [];

    /** @var array  */
    protected $channelGroups = [];

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
     * @param string|string[] $groups
     * @return $this
     */
    public function channelGroups($groups)
    {
        $this->channelGroups = PubNubUtil::extendArray($this->channelGroups, $groups);

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        $this->validateChannelGroups($this->channels, $this->channelGroups);
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

        if (count($this->channelGroups) > 0) {
            $params['channel-group'] = PubNubUtil::joinItems($this->channelGroups);
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
     * @return PNGetStateResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $json Decoded json
     * @return PNGetStateResult
     */
    public function createResponse($json)
    {
        if (count($this->channels) === 1 && count($this->channelGroups) === 0) {
            $channels = [$this->channels[0] => $json['payload']];
        } else if (count($this->channels) === 0 && count($this->channelGroups) === 1) {
            $channels = [$this->channelGroups[0] => $json['payload']];
        } else {
            $channels = $json['payload']['channels'];
        }

        return new PNGetStateResult($channels);
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    /**
     * @return string|string[]
     */
    public function getAffectedChannels()
    {
        return $this->channels;
    }

    /**
     * @return string|string[]
     */
    public function getAffectedChannelGroups()
    {
        return $this->channelGroups;
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

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNGetState;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Grant";
    }
}