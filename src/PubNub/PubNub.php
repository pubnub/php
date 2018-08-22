<?php

namespace PubNub;

use Monolog\Logger;
use PubNub\Builders\SubscribeBuilder;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Endpoints\Access\Audit;
use PubNub\Endpoints\Access\Grant;
use PubNub\Endpoints\Access\Revoke;
use PubNub\Endpoints\ChannelGroups\AddChannelToChannelGroup;
use PubNub\Endpoints\ChannelGroups\ListChannelsInChannelGroup;
use PubNub\Endpoints\ChannelGroups\RemoveChannelFromChannelGroup;
use PubNub\Endpoints\ChannelGroups\RemoveChannelGroup;
use PubNub\Endpoints\History;
use PubNub\Endpoints\HistoryDelete;
use PubNub\Endpoints\Presence\GetState;
use PubNub\Endpoints\Presence\HereNow;
use PubNub\Endpoints\Presence\SetState;
use PubNub\Endpoints\Presence\WhereNow;
use PubNub\Endpoints\PubSub\Publish;
use PubNub\Endpoints\Push\AddChannelsToPush;
use PubNub\Endpoints\Push\ListPushProvisions;
use PubNub\Endpoints\Push\RemoveChannelsFromPush;
use PubNub\Endpoints\Push\RemoveDeviceFromPush;
use PubNub\Endpoints\Time;
use PubNub\Managers\BasePathManager;
use PubNub\Managers\SubscriptionManager;
use PubNub\Managers\TelemetryManager;


class PubNub
{
    const SDK_VERSION = "4.0.0";
    const SDK_NAME = "PubNub-PHP";

    public static $MAX_SEQUENCE = 65535;

    /** @var PNConfiguration */
    protected $configuration;

    /** @var  BasePathManager */
    protected $basePathManager;

    /** @var  SubscriptionManager */
    protected $subscriptionManager;

    /** @var TelemetryManager */
    protected $telemetryManager;

    /** @var  Logger */
    protected $logger;

    /** @var  int $nextSequence */
    protected $nextSequence = 0;

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
        $this->telemetryManager = new TelemetryManager();
        $this->logger = new Logger('PubNub');
    }

    /**
     * Pre-configured PubNub client with demo-keys
     * @return static
     */
    public static function demo()
    {
        return new static(PNConfiguration::demoKeys());
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function addListener($listener)
    {
        $this->subscriptionManager->addListener($listener);
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function removeListener($listener)
    {
        $this->subscriptionManager->removeListener($listener);
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
     * @return AddChannelsToPush
     */
    public function addChannelsToPush()
    {
        return new AddChannelsToPush($this);
    }

    /**
     * @return RemoveChannelsFromPush
     */
    public function removeChannelsFromPush()
    {
        return new RemoveChannelsFromPush($this);
    }

    /**
     * @return RemoveDeviceFromPush
     */
    public function removeAllPushChannelsForDevice()
    {
        return new RemoveDeviceFromPush($this);
    }

    /**
     * @return ListPushProvisions
     */
    public function listPushProvisions()
    {
        return new ListPushProvisions($this);
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

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return GetState
     */
    public function getState()
    {
        return new GetState($this);
    }

    /**
     * @return SetState
     */
    public function setState()
    {
        return new SetState($this);
    }

    /**
     * @return HistoryDelete
     */
    public function deleteMessages()
    {
        return new HistoryDelete($this);
    }

    /**
     * @return TelemetryManager
     */
    public function getTelemetryManager()
    {
        return $this->telemetryManager;
    }

    /**
     * @return int unique sequence identifier
     */
    public function getSequenceId()
    {
        if (static::$MAX_SEQUENCE === $this->nextSequence) {
            $this->nextSequence = 1;
        } else {
            $this->nextSequence += 1;
        }

        return $this->nextSequence;
    }
}
