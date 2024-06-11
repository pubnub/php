<?php

namespace PubNub\Endpoints\Push;

use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNPushType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Push\PNPushRemoveChannelResult;
use PubNub\PubNubUtil;

class RemoveChannelsFromPush extends PushEndpoint
{
    protected const OPERATION_TYPE = PNOperationType::PNRemovePushNotificationsFromChannelsOperation;
    protected const OPERATION_NAME = "RemoveChannelsFromPush";
    public const PATH = "/v1/push/sub-key/%s/devices/%s";
    public const PATH_APNS2 = "/v2/push/sub-key/%s/devices-apns2/%s";

    /** @var  string | string[] */
    protected string | array $channels = [];

    /**
     * @param string|string[] $channels
     * @return $this
     */
    public function channels(string | array $channels): static
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $channels);
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

        if (($this->pushType == PNPushType::APNS2) && (!is_string($this->topic) || strlen($this->topic) === 0)) {
            throw new PubNubValidationException("APNS2 topic is missing");
        }
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [
            'remove' => PubNubUtil::joinItems($this->channels),
        ];

        if ($this->pushType != PNPushType::APNS2) {
            // v1 push -> add type
            $params['type'] = $this->pushType;
        } else {
            // apns2 push -> add topic and environment
            $params['topic'] = $this->topic;
            $params['environment'] = $this->environment ?? 'development';
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
        $path = $this->pushType == PNPushType::APNS2 ? static::PATH_APNS2 : static::PATH;

        return sprintf(
            $path,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->deviceId
        );
    }

    /**
     * @param array $json Decoded json
     * @return PNPushRemoveChannelResult
     */
    protected function createResponse($json)
    {
        return new PNPushRemoveChannelResult();
    }
}
