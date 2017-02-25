<?php

namespace PubNub\Endpoints\PubSub;


use PubNub\Builders\PubNubErrorBuilder;
use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\PNPublishResult;
use PubNub\PubNubException;
use PubNub\PubNubUtil;

class Publish extends Endpoint
{
    const GET_PATH = "/publish/%s/%s/0/%s/%s/%s";
    const POST_PATH = "/publish/%s/%s/0/%s/%s";

    /** @var  mixed $message to publish */
    private $message;

    /** @var  string $channel to send message on*/
    private $channel;

    /** @var  bool $shouldStore in history */
    private $shouldStore;

    /** @var bool $usePost HTTP method instead of default GET  */
    private $usePost;

    /** @var  array $meta data */
    private $meta;

    // TODO: not sure what it means
    private $replicate;

    /** @var  int $ttl in storage (min ?)*/
    private $ttl;

    /** @var  int $sequenceCounter */
    private $sequenceCounter;

    /**
     * @return PNPublishResult
     */
    public function sync()
    {
        return parent::sync();
    }

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
     * @param mixed $replicate
     */
    public function setReplicate($replicate)
    {
        $this->replicate = $replicate;
    }

    /**
     * @param int $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

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
     * @param array $json Decoded json
     * @return PNPublishResult
     */
    protected function createResponse($json)
    {
        $timetoken = (int) $json[2];

        $response = new PNPublishResult($timetoken);

        return $response;
    }

    protected function getOperationType()
    {
        return PNOperationType::PNPublishOperation;
    }

    protected function isAuthRequired()
    {
        return true;
    }

    protected function httpMethod()
    {
        return $this->usePost ? PNHttpMethod::POST : PNHttpMethod::GET;
    }

    /**
     * @return int unique sequence identifier
     */
    private function getSequenceId()
    {
        return $this->sequenceCounter++;
    }
}
