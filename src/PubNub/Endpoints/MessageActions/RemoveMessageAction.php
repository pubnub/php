<?php

namespace PubNub\Endpoints\MessageActions;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\MessageActions\PNRemoveMessageActionResult;

/** @package PubNub\Endpoints\MessageActions */
class RemoveMessageAction extends Endpoint
{
    protected bool $endpointAuthRequired = true;
    protected int $endpointConnectTimeout;
    protected int $endpointRequestTimeout;
    protected string $endpointHttpMethod = PNHttpMethod::DELETE;
    protected int $endpointOperationType = PNOperationType::PNGetMessageActionOperation;
    protected string $endpointName = "Get Message Actions";

    protected const DELETE_PATH = "/v1/message-actions/%s/channel/%s/message/%s/action/%s";
    protected string $channel;
    protected string $messageTimetoken;
    protected string $actionTimetoken;

    public function __construct($pubnub)
    {
        parent::__construct($pubnub);
        $this->endpointConnectTimeout = $this->pubnub->getConfiguration()->getConnectTimeout();
        $this->endpointRequestTimeout = $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * Set a channel for the message action
     *
     * @param string $channel
     * @return RemoveMessageAction
     */
    public function channel(string $channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * The publish timetoken of a parent message.
     *
     * @param string $messageTimetoken
     * @return RemoveMessageAction
     */
    public function messageTimetoken(string $messageTimetoken): self
    {
        $this->messageTimetoken = $messageTimetoken;
        return $this;
    }

    /**
     * The publish timetoken of the reaction.
     *
     * @param string $actionTimetoken
     * @return RemoveMessageAction
     */
    public function actionTimetoken(string $actionTimetoken): self
    {
        $this->actionTimetoken = $actionTimetoken;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        if (!$this->channel) {
            throw new PubNubValidationException("Channel Missing");
        }

        if (!$this->messageTimetoken) {
            throw new PubNubValidationException("Message Timetoken Missing");
        }

        if (!$this->actionTimetoken) {
            throw new PubNubValidationException("Action Timetoken Missing");
        }
        $this->validateSubscribeKey();
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function buildData()
    {
        return [];
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildPath()
    {
        return sprintf(
            self::DELETE_PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channel,
            $this->messageTimetoken,
            $this->actionTimetoken
        );
    }

    /**
     * @return PNRemoveMessageActionResult
     */
    public function sync(): PNRemoveMessageActionResult
    {
        return parent::sync();
    }

    /**
     * @param array $json Decoded json
     * @return PNPublishResult
     */
    protected function createResponse($json): PNRemoveMessageActionResult
    {
        return new PNRemoveMessageActionResult();
    }
}
