<?php

namespace PubNub\Models\Server;


use PubNub\Models\Server\PublishMetadata;

class SubscribeMessage
{
    /** @var  string */
    private $shard;

    /** @var  string */
    private $subscriptionMatch;

    /** @var  string */
    private $channel;

    /** @var  array */
    private $payload;

    /** @var  string */
    private $flags;

    /** @var  string */
    private $issuingClientId;

    /** @var  string */
    private $subscribeKey;

    // TODO: specify OriginationMetaData
    private $originationMetadata;

    /** @var  PublishMetadata */
    private $publishMetaData;

    /**
     * @param array $jsonInput
     * @return SubscribeMessage
     */
    public static function fromJson($jsonInput)
    {
        $message = new static();

        $message->shard = $jsonInput['a'];

        if (array_key_exists('b', $jsonInput)) {
            $message->subscriptionMatch = $jsonInput['b'];
        }

        $message->channel = $jsonInput['c'];
        $message->payload = $jsonInput['d'];
        $message->flags = $jsonInput['f'];

        if (array_key_exists('i', $jsonInput)) {
            $message->issuingClientId = $jsonInput['i'];
        }

        $message->subscribeKey = $jsonInput['k'];

        if (array_key_exists('o', $jsonInput)) {
            $message->originationMetadata = $jsonInput['o'];
        }

        $message->publishMetaData = PublishMetadata::fromJson($jsonInput['p']);

        return $message;
    }

    /**
     * @return string
     */
    public function getShard()
    {
        return $this->shard;
    }

    /**
     * @return string
     */
    public function getSubscriptionMatch()
    {
        return $this->subscriptionMatch;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @return string
     */
    public function getIssuingClientId()
    {
        return $this->issuingClientId;
    }

    /**
     * @return string
     */
    public function getSubscribeKey()
    {
        return $this->subscribeKey;
    }

    /**
     * @return PublishMetadata
     */
    public function getPublishMetaData()
    {
        return $this->publishMetaData;
    }
}