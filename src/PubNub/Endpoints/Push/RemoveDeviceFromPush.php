<?php

namespace PubNub\Endpoints\Push;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNPushType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Push\PNPushRemoveAllChannelsResult;


class RemoveDeviceFromPush extends Endpoint
{
    const PATH = "/v1/push/sub-key/%s/devices/%s/remove";

    const PATH_APNS2 = "/v2/push/sub-key/%s/devices-apns2/%s/remove";

    /** @var  string */
    protected $deviceId;

    /** @var  string */
    protected $pushType;

    /** @var  string */
    protected $environment;

    /** @var  string */
    protected $topic;

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
        // FCM is new, GCM is still used internally
        if( $pushType == PNPushType::FCM )
        {
            $pushType = PNPushType::GCM;
        }

        $this->pushType = $pushType;

        return $this;
    }

    /**
     * @param int $environment
     * @return $this
     */
    public function environment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @param int $pushType
     * @return $this
     */
    public function topic($topic)
    {
        $this->topic = $topic;

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

        if (($this->pushType == PNPushType::APNS2) && (!is_string($this->topic) || strlen($this->topic) === 0)) {
            throw new PubNubValidationException("APNS2 topic is missing");
        }
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        if ($this->pushType != PNPushType::APNS2) {
            // v1 push -> add type
            $params['type'] = $this->pushType;
        } else {
            // apns2 push -> add topic and environment
            $params['topic'] = $this->topic;

            if (is_string($this->environment) && strlen($this->environment) > 0) {
                $params['environment'] = $this->environment;
            } else {
                $params['environment'] = 'development';
            }
        }

        return $params;
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
        $path = $this->pushType == PNPushType::APNS2 ? RemoveDeviceFromPush::PATH_APNS2 : RemoveDeviceFromPush::PATH;

        return sprintf(
            $path,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->deviceId
        );
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
