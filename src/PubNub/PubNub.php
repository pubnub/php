<?php

namespace PubNub;

use PubNub\Endpoints\PubSub\Publish;
use PubNub\Endpoints\Time;
use PubNub\Managers\BasePathManager;

class PubNub
{
    const SDK_VERSION = "4.0.0.alpha.1";
    const SDK_NAME = "PubNub-PHP";

    /** @var PNConfiguration  */
    private $configuration;

    /** @var  BasePathManager */
    private $basePathManager;

    /**
     * PNConfiguration constructor.
     *
     * @param $initialConfig PNConfiguration
     */
    public function __construct($initialConfig)
    {
        $this->configuration = $initialConfig;
        $this->basePathManager = new BasePathManager($initialConfig);
    }

    public function publish()
    {
        return new Publish($this);
    }

    public function time()
    {
        return new Time($this);
    }

    public function getVersion()
    {
        return static::SDK_VERSION;
    }

    public function getName()
    {
        return static::SDK_NAME;
    }

    public function getFullName()
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
