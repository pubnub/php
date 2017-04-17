<?php

namespace PubNub\Endpoints\Push;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Push\PNPushRemoveAllChannelsResult;


class RemoveDeviceFromPush extends Endpoint
{
    const PATH = "/v1/push/sub-key/%s/devices/%s/remove";

    /** @var  string */
    protected $deviceId;

    /** @var  string */
    protected $pushType;

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
            RemoveDeviceFromPush::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->deviceId);
    }

    /**
     * @param array $json Decoded json
     * @return PNPushRemoveAllChannelsResult
     */
    protected function createResponse($json)
    {
        return new PNPushRemoveAllChannelsResult();
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
        return PNOperationType::PNRemoveAllPushNotificationsOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "RemoveDeviceFromPush";
    }
}
