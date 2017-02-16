<?php

namespace PubNub\Endpoints\Access;


use PubNub\Endpoints\Endpoint;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNubUtil;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;


class Grant extends Endpoint
{
    const GRANT_PATH = "/v1/auth/grant/sub-key/%s";

    protected $authKeys = [];
    protected $channels = [];
    protected $groups = [];
    protected $read;
    protected $write;
    protected $manage;
    protected $ttl;

    protected $sortParams = true;

    public function authKeys($authKeys)
    {
        PubNubUtil::extendArray($this->authKeys, $authKeys);
        return $this;
    }

    public function channels($channels)
    {
        PubNubUtil::extendArray($this->channels, $channels);
        return $this;
    }

    public function channelGroups($channelsGroups)
    {
        PubNubUtil::extendArray($this->groups, $channelsGroups);
        return $this;
    }

    public function read($flag)
    {
        $this->read = $flag;
        return $this;
    }

    public function write($flag)
    {
        $this->write = $flag;
        return $this;
    }

    public function manage($flag)
    {
        $this->manage = $flag;
        return $this;
    }

    public function ttl($flag)
    {
        $this->ttl = $flag;
        return $this;
    }

    public function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateSecretKey();

        if ($this->write === null && $this->read === null && $this->manage === null) {
            throw new PubNubValidationException("At least one flag should be specified");
        }
    }

    public function buildData()
    {
        return null;
    }

    public function buildParams()
    {
        $params = $this->defaultParams();

        if ($this->read !== null) {
            $params["r"] = ($this->read) ? "1" : "0";
        }

        if ($this->write !== null) {
            $params["w"] = ($this->read) ? "1" : "0";
        }

        if ($this->manage !== null) {
            $params["m"] = ($this->read) ? "1" : "0";
        }

        if (count($this->authKeys) > 0) {
            $params["auth"] = PubNubUtil::joinItems($this->authKeys);
        }

        if (count($this->channels) > 0) {
            $params["channel"] = PubNubUtil::joinItems($this->channels);
        }

        if (count($this->groups) > 0) {
            $params["channel-group"] = PubNubUtil::joinItems($this->groups);
        }

        if (count($this->ttl) > 0) {
            $params["ttl"] = $this->ttl;
        }

        return $params;
    }

    public function buildPath()
    {
        return Grant::GRANT_PATH % $this->pubnub->getConfiguration()->getSubscribeKey();
    }

    public function httpMethod()
    {
        PNHttpMethod::GET;
    }

    public function createResponse($json)
    {
        return null;
    }

    public function isAuthRequired()
    {
        return false;
    }

    public function getAffectedChannels()
    {
        return $this->channels;
    }

    public function getAffectedChannelGroups()
    {
        return $this->groups;
    }

    public function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    public function getConnectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    public function getOperationType()
    {
        return PNOperationType::PNAccessManagerGrant;
    }

    public function getName()
    {
        return "Grant";
    }
}

