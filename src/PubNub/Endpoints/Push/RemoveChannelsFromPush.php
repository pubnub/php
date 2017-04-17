<?php

namespace PubNub\Endpoints\Push;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Push\PNPushRemoveChannelResult;
use PubNub\PubNubUtil;


class RemoveChannelsFromPush extends Endpoint
{
    const PATH = "/v1/push/sub-key/%s/devices/%s";

    /** @var  string[] */
    protected $channels = [];

    /** @var  string */
    protected $deviceId;

    /** @var  string */
    protected $pushType;

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
     * @param string $deviceId
     * @return $this
     */
    public function deviceId($deviceId)
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    /**
     * @param int $pushType
     * @return $this
     */
    public function pushType($pushType)
    {
        $this->pushType = $pushType;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (!is_array($this->channels) || count($this->channels) === 0) {
            throw new PubNubValidationException("Channel missing");
        }

        if (!is_string($this->deviceId) || strlen($this->deviceId) === 0) {
            throw new PubNubValidationException("Device ID is missing for push operation");
        }

        if ($this->pushType === null || strlen($this->pushType) === 0) {
            throw new PubNubValidationException("Push Type is missing");
        }
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        return [
            'remove' => PubNubUtil::joinItems($this->channels),
            'type' => $this->pushType
        ];
    }

    /**
     * @return null
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
            RemoveChannelsFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->deviceId);
    }

    /**
     * @param array $json Decoded json
     * @return PNPushRemoveChannelResult
     */
    protected function createResponse($json)
    {
        return new PNPushRemoveChannelResult();
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
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

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNRemovePushNotificationsFromChannelsOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "RemoveChannelsFromPush";
    }
}
