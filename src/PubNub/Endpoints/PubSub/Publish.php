<?php

namespace PubNub\Endpoints\PubSub;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubBuildRequestException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\PNPublishResult;
use PubNub\PubNubUtil;

final class Publish extends Endpoint
{
    private const GET_PATH = "/publish/%s/%s/0/%s/%s/%s";
    private const POST_PATH = "/publish/%s/%s/0/%s/%s";

    /** @var  mixed $message to publish */
    protected mixed $message;

    /** @var  string $channel to send message to */
    protected string $channel;

    /** @var  bool $shouldStore in history */
    protected ?bool $shouldStore;

    /** @var bool $usePost HTTP method instead of default GET  */
    protected $usePost;

    /** @var string $customMessageType User defined message type */
    protected ?string $customMessageType;

    /** @var  array $meta data */
    protected $meta;

    /** @var  int $ttl in storage (min ?)*/
    protected $ttl;

    /** @var  bool */
    protected $replicate = true;

    /** @var  bool */
    protected $serialize = true;

    /**
     * @param mixed $message
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param string $channel
     * @return $this
     */
    public function channel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return $this
     */
    public function doNotSerialize()
    {
        $this->serialize = false;

        return $this;
    }

    /**
     * @param bool $shouldStore
     * @return $this
     */
    public function shouldStore($shouldStore)
    {
        $this->shouldStore = $shouldStore;

        return $this;
    }

    /**
     * @param bool $usePost
     * @return $this
     */
    public function usePost($usePost)
    {
        $this->usePost = $usePost;

        return $this;
    }

    /**
     * @param string $customMessageType
     * @return $this
     */
    public function customMessageType(?string $customMessageType)
    {
        $this->customMessageType = $customMessageType;

        return $this;
    }

    /**
     * @param array $meta
     * @return $this
     */
    public function meta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @param bool $replicate
     * @return $this
     */
    public function replicate($replicate)
    {
        $this->replicate = $replicate;

        return $this;
    }

    /**
     * @param int $ttl
     * @return $this
     */
    public function ttl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        if (!isset($this->message)) {
            throw new PubNubValidationException("Message Missing");
        }

        if (!isset($this->channel) || !is_string($this->channel) || strlen($this->channel) === 0) {
            throw new PubNubValidationException("Channel Missing");
        }

        $this->validateSubscribeKey();
        $this->validatePublishKey();
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        if (isset($this->meta)) {
            $params['meta'] = PubNubUtil::writeValueAsString($this->meta);
        }

        if (isset($this->shouldStore)) {
            if ($this->shouldStore) {
                $params['store'] = "1";
            } else {
                $params['store'] = "0";
            }
        }

        if (isset($this->ttl)) {
            $params['ttl'] = (string) $this->ttl;
        }

        if (isset($this->customMessageType)) {
            $params['custom_message_type'] = $this->customMessageType;
        }

        $params['seqn'] = $this->pubnub->getSequenceId();

        if (!$this->replicate) {
            $params['norep'] = 'true';
        }

        return $params;
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildData()
    {
        if ($this->usePost == true) {
            if ($this->serialize) {
                $msg = PubNubUtil::writeValueAsString($this->message);
            } else {
                if (!is_string($this->message)) {
                    throw new PubNubBuildRequestException("Type error, only string is expected");
                } else {
                    $msg = $this->message;
                }
            }

            if ($this->pubnub->getConfiguration()->isAesEnabled()) {
                return '"' . $this->pubnub->getConfiguration()->getCrypto()->encrypt($msg) . '"';
            } else {
                return $msg;
            }
        } else {
            return null;
        }
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildPath()
    {
        if ($this->usePost) {
            return sprintf(
                static::POST_PATH,
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                PubNubUtil::urlEncode($this->channel),
                0
            );
        } else {
            if ($this->serialize) {
                $stringifiedMessage = PubNubUtil::writeValueAsString($this->message);
            } else {
                if (!is_string($this->message)) {
                    throw new PubNubBuildRequestException("Type error, only string is expected");
                } else {
                    $stringifiedMessage = $this->message;
                }
            }

            if ($this->pubnub->getConfiguration()->isAesEnabled()) {
                $stringifiedMessage = "\"" .
                    $this->pubnub->getConfiguration()->getCrypto()->encrypt($stringifiedMessage) . "\"";
            }

            $stringifiedMessage = PubNubUtil::urlEncode($stringifiedMessage);

            return sprintf(
                static::GET_PATH,
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                PubNubUtil::urlEncode($this->channel),
                0,
                $stringifiedMessage
            );
        }
    }

    /**
     * @return PNPublishResult
     */
    public function sync(): PNPublishResult
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNPublishResult
     */
    protected function createResponse($result): PNPublishResult
    {
        $timetoken = floatval($result[2]);

        return new PNPublishResult($timetoken);
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
     * @return string
     */
    protected function httpMethod()
    {
        return $this->usePost ? PNHttpMethod::POST : PNHttpMethod::GET;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNPublishOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "Publish";
    }
}
