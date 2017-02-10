<?php

namespace PubNub;

use PubNub\Builders\SubscribeBuilder;
use PubNub\Endpoints\PubSub\Publish;
use PubNub\Endpoints\Time;
use PubNub\Managers\BasePathManager;
use PubNub\Managers\SubscriptionManager;

class PubNub
{
    const SDK_VERSION = "4.0.0.alpha.1";
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

    public function publish()
    {
        return new Publish($this);
    }

    public function subscribe()
    {
        return new SubscribeBuilder($this->subscriptionManager);
    }

    public function time()
    {
        return new Time($this);
    }

    public function getSdkVersion()
    {
        return static::SDK_VERSION;
    }

    public function getSdkName()
    {
        return static::SDK_NAME;
    }

    public function getSdkFullName()
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
