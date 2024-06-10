<?php

namespace PubNub\Endpoints\Push;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNPushType;
use PubNub\Exceptions\PubNubValidationException;

abstract class PushEndpoint extends Endpoint
{
    protected const OPERATION_TYPE = null;
    protected const OPERATION_NAME = null;
    protected string $deviceId;
    protected string $pushType;
    protected string $environment;
    protected string $topic;

    /**
     * @param string $deviceId
     * @return $this
     */
    public function deviceId(string $deviceId): static
    {
        $this->deviceId = $deviceId;
        return $this;
    }

    /**
     * @param string $environment
     * @return $this
     */
    public function environment(string $environment): static
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @param string  $topic
     * @return $this
     */
    public function topic(string $topic): static
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     * @param string $pushType
     * @return $this
     */
    public function pushType(string $pushType): static
    {
        $this->pushType = $pushType;
        return $this;
    }

    protected function validatePushType()
    {
        if ($this->pushType === null || strlen($this->pushType) === 0) {
            throw new PubNubValidationException("Push type missing");
        }

        if ($this->pushType === PNPushType::GCM) {
            trigger_error("GCM is deprecated. Please use FCM instead.", E_USER_DEPRECATED);
        }

        if (
            !in_array(
                $this->pushType,
                [PNPushType::APNS, PNPushType::APNS2, PNPushType::MPNS, PNPushType::GCM, PNPushType::FCM]
            )
        ) {
            throw new PubNubValidationException("Invalid push type");
        }
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateDeviceId()
    {
        if (!is_string($this->deviceId) || strlen($this->deviceId) === 0) {
            throw new PubNubValidationException("Device ID is missing for push operation");
        }
    }

    protected function validateTopic()
    {
        if (($this->pushType == PNPushType::APNS2) && (!is_string($this->topic) || strlen($this->topic) === 0)) {
            throw new PubNubValidationException("APNS2 topic is missing");
        }
    }

     /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateDeviceId();
        $this->validatePushType();
        $this->validateTopic();
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
        return static::OPERATION_TYPE;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return static::OPERATION_NAME;
    }
}
