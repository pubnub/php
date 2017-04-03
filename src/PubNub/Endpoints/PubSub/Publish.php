<?php

namespace PubNub\Endpoints\PubSub;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\PNPublishResult;
use PubNub\PubNubUtil;


class Publish extends Endpoint
{
    const GET_PATH = "/publish/%s/%s/0/%s/%s/%s";
    const POST_PATH = "/publish/%s/%s/0/%s/%s";

    /** @var  mixed $message to publish */
    protected $message;

    /** @var  string $channel to send message on*/
    protected $channel;

    /** @var  bool $shouldStore in history */
    protected $shouldStore;

    /** @var bool $usePost HTTP method instead of default GET  */
    protected $usePost;

    /** @var  array $meta data */
    protected $meta;

    /** @var  int $ttl in storage (min ?)*/
    protected $ttl;

    /** @var  int $sequenceCounter */
    protected $sequenceCounter = 0;

    /** @var  bool */
    protected $replicate = true;

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
     * @param bool $shouldStore
     * @return $this
     */
    public function setShouldStore($shouldStore)
    {
        $this->shouldStore = $shouldStore;

        return $this;
    }

    /**
     * @param bool $usePost
     * @return $this
     */
    public function setUsePost($usePost)
    {
        $this->usePost = $usePost;

        return $this;
    }

    /**
     * @param array $meta
     * @return $this
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @param bool $replicate
     * @return $this
     */
    public function setReplicate($replicate)
    {
        $this->replicate = $replicate;

        return $this;
    }

    /**
     * @param int $ttl
     * @return $this
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        if ($this->message === null) {
            throw new PubNubValidationException("Message Missing");
        }

        if (empty($this->channel)) {
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

        if ($this->meta !== null) {
            $params['meta'] = PubNubUtil::urlWrite($this->meta);
        }

        if ($this->shouldStore !== null) {
            if ($this->shouldStore) {
                $params['store'] = "1";
            } else {
                $params['store'] = "0";
            }
        }

        if ($this->ttl !== null) {
            $params['ttl'] = (string) $this->ttl;
        }

        $params['seqn'] = $this->getSequenceId();

        if (!$this->replicate) {
            $params['norep'] = 'true';
        }

        return $params;
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        if ($this->usePost == true) {
            $msg = PubNubUtil::writeValueAsString($this->message);

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
     */
    protected function buildPath()
    {
        if ($this->usePost) {
            return sprintf(
                static::POST_PATH,
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                $this->channel,
                0
            );
        } else {
            $stringifiedMessage = PubNubUtil::writeValueAsString($this->message);

            if ($this->pubnub->getConfiguration()->isAesEnabled()) {
                $stringifiedMessage = "\"" .
                    $this->pubnub->getConfiguration()->getCrypto()->encrypt($stringifiedMessage) . "\"";
            }

            $stringifiedMessage = PubNubUtil::urlEncode($stringifiedMessage);

            return sprintf(
                static::GET_PATH,
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                $this->channel,
                0,
                $stringifiedMessage
            );
        }
    }

    /**
     * @return PNPublishResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $json Decoded json
     * @return PNPublishResult
     */
    protected function createResponse($json)
    {
        $timetoken = (int) $json[2];

        return new PNPublishResult($timetoken);
    }

    /**
     * @return int unique sequence identifier
     */
    private function getSequenceId()
    {
        return $this->sequenceCounter++;
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
        return "SetState";
    }
}
