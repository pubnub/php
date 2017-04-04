<?php

namespace PubNub;

use PubNub\Builders\SubscribeBuilder;
use PubNub\Endpoints\Access\Audit;
use PubNub\Endpoints\Access\Grant;
use PubNub\Endpoints\Access\Revoke;
use PubNub\Endpoints\ChannelGroups\AddChannelToChannelGroup;
use PubNub\Endpoints\ChannelGroups\ListChannelsInChannelGroup;
use PubNub\Endpoints\ChannelGroups\RemoveChannelFromChannelGroup;
use PubNub\Endpoints\ChannelGroups\RemoveChannelGroup;
use PubNub\Endpoints\History;
use PubNub\Endpoints\Presence\HereNow;
use PubNub\Endpoints\Presence\WhereNow;
use PubNub\Endpoints\PubSub\Publish;
use PubNub\Endpoints\Time;
use PubNub\Managers\BasePathManager;
use PubNub\Managers\SubscriptionManager;

class PubNub
{
    const SDK_VERSION = "4.0.0-alpha";
    const SDK_NAME = "PubNub-PHP";

    /** @var PNConfiguration  */
    private $configuration;

    /** @var  BasePathManager */
    private $basePathManager;

    /** @var  SubscriptionManager */
    private $subscriptionManager;

    /**
     * PNConfiguration constructor.
     *
     * @param $initialConfig PNConfiguration
     */
    public function __construct($initialConfig)
    {
        $this->configuration = $initialConfig;
        $this->basePathManager = new BasePathManager($initialConfig);
        $this->subscriptionManager = new SubscriptionManager($this);
    }

    public function addListener($listener)
    {
        $this->subscriptionManager->addListener($listener);
    }

    /**
     * @return Publish
     */
    public function publish()
    {
        return new Publish($this);
    }

    /**
     * @return SubscribeBuilder
     */
    public function subscribe()
    {
        return new SubscribeBuilder($this->subscriptionManager);
    }

    /**
     * @return History
     */
    public function history()
    {
        return new History($this);
    }

    /**
     * @return HereNow
     */
    public function hereNow()
    {
        return new HereNow($this);
    }

    /**
     * @return WhereNow
     */
    public function whereNow()
    {
        return new WhereNow($this);
    }

    /**
     * @return Grant
     */
    public function grant()
    {
        return new Grant($this);
    }

    /**
     * @return Audit
     */
    public function audit()
    {
        return new Audit($this);
    }

    /**
     * @return Revoke
     */
    public function revoke()
    {
        return new Revoke($this);
    }

    /**
     * @return AddChannelToChannelGroup
     */
    public function addChannelToChannelGroup()
    {
        return new AddChannelToChannelGroup($this);
    }

    /**
     * @return RemoveChannelFromChannelGroup
     */
    public function removeChannelFromChannelGroup()
    {
        return new RemoveChannelFromChannelGroup($this);
    }

    /**
     * @return RemoveChannelGroup
     */
    public function removeChannelGroup()
    {
        return new RemoveChannelGroup($this);
    }

    /**
     * @return ListChannelsInChannelGroup
     */
    public function listChannelsInChannelGroup()
    {
        return new ListChannelsInChannelGroup($this);
    }

    /**
     * @return Time
     */
    public function time()
    {
        return new Time($this);
    }

    /**
     * @return int
     */
    public function timestamp()
    {
        return time();
    }

    /**
     * @return string
     */
    static public function getSdkVersion()
    {
        return static::SDK_VERSION;
    }

    /**
     * @return string
     */
    static public function getSdkName()
    {
        return static::SDK_NAME;
    }

    /**
     * @return string
     */
    static public function getSdkFullName()
    {
        $fullName = static::SDK_NAME . "/" . static::SDK_VERSION;

        return $fullName;
    }

    /**
     * Get PubNub configuration object
     *
     * @return PNConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return string Base path
     */
    public function getBasePath()
    {
        return $this->basePathManager->getBasePath();
    }
}
