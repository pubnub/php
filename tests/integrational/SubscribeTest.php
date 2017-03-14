<?php

namespace Tests\Integrational;

require __DIR__ . '/../../vendor/autoload.php';

use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\PNConfiguration;
use Requests;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PubNub;

Requests::request("https://httpstatuses.com/200");

const CHANNEL = 'ch1';
const MESSAGE = 'hey';
const GROUP = 'gr1';


class AutoloadingWorker extends \Worker
{
    public function run()
    {
        require __DIR__ . '/../../src/autoloader.php';
    }
}


class SubscribeTest extends \PubNubTestCase
{
    public function testSubscribeUnsubscribe()
    {
        $this->pubnub->addListener(new MySubscribeCallback($this->config));
        $this->pubnub->subscribe()->channels(CHANNEL)->execute();
    }

    public function testSubscribePublishUnsubscribeSingleChannel()
    {
        $this->pubnub->addListener(new MySubscribePublishCallback($this->config));
        $this->pubnub->subscribe()->channels(CHANNEL)->execute();
    }

    public function testEncryptedSubscribePublish()
    {
        $this->pubnub_enc->addListener(new MySubscribeCallback($this->config_enc));
        $this->pubnub_enc->subscribe()->channels(CHANNEL)->execute();
    }

    public function testCGSubscribeUnsubscribe()
    {
        $this->pubnub->addListener(new MySubscribeCGCallback($this->config));
        $this->pubnub->addChannelToChannelGroup()->channels(CHANNEL)->group(GROUP)->sync();
        $this->pubnub->subscribe()->channelGroups(GROUP)->execute();
    }
}


class MySubscribeCallback extends SubscribeCallback
{
    protected $config;

    /**
     * MySubscribeCallback constructor.
     * @param $config
     */
    function __construct(PNConfiguration $config)
    {
        $this->config = $config;
    }

    function status($pubnub, $status)
    {
        if ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            $publishThread = new PublishThread($this->config);

            $worker = new AutoloadingWorker();
            $worker->stack($publishThread);
            $worker->start();
        }
    }

    /**
     * @param $pubnub
     * @param PNMessageResult $message
     * @throws PubNubUnsubscribeException
     */
    function message($pubnub, $message)
    {
        if ($message->getMessage() === MESSAGE) {
            throw new PubNubUnsubscribeException();
        }
    }

    function presence($pubnub, $presence)
    {
    }
}


class MySubscribePublishCallback extends SubscribeCallback
{
    /** @var  PNConfiguration */
    protected $config;

    function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param PubNub $pubnub
     * @param PNStatus $status
     */
    function status($pubnub, $status)
    {
        if ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            $publishThread = new PublishThread($this->config, false);

            $worker = new AutoloadingWorker();
            $worker->stack($publishThread);
            $worker->start();
        }
    }

    /**
     * @param PubNub $pubnub
     * @param PNMessageResult $message
     * @throws PubNubUnsubscribeException
     */
    function message($pubnub, $message)
    {
        if ($message->getMessage() === MESSAGE) {
            throw new PubNubUnsubscribeException();
        }
    }

    function presence($pubnub, $presence)
    {
    }
}


class MySubscribeCGCallback extends SubscribeCallback
{
    protected $config;

    function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param PubNub $pubnub
     * @param PNStatus $status
     */
    function status($pubnub, $status)
    {
        if ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            $pubnub->removeChannelFromChannelGroup()->group(GROUP)->channels(CHANNEL)->sync();
        }
    }

    function message($pubnub, $message)
    {
    }

    function presence($pubnub, $presence)
    {
    }
}


class PublishThread extends \Thread {
    /** @var  PNConfiguration */
    protected $config;

    /**
     * PublishCallback constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function run()
    {
        $pubnub = new PubNub($this->config);

        $pubnub->publish()->channel(CHANNEL)->message(MESSAGE)->sync();
    }

    function message($pubnub, $message)
    {
    }

    function presence($pubnub, $presence)
    {
    }
}

