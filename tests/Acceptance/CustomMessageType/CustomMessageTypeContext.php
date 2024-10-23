<?php

namespace PubNubTests\Acceptance\CustomMessageType;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PubNub\PubNub;
use PubNub\PNConfiguration;
use PubNub\Models\Consumer\PNPublishResult;
use PubNub\Models\Consumer\MessagePersistence\PNFetchMessagesResult;
use PubNub\Models\Consumer\PubSub\PNSignalResult;
use PubNubTests\Acceptance\PubNubContext;
use PubNub\Exceptions\PubNubServerException;

/**
 * Defines application features from the specific context.
 */
class CustomMessageTypeContext extends PubNubContext implements Context
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */

    private PubNub $pubnub;
    private PNConfiguration $config;
    private string $channelName;
    private $response;


    public function __construct()
    {
        $this->config = new PNConfiguration();
    }

    /**
     * @Given the demo keyset
     */
    public function theDemoKeyset()
    {
        $this->config->setOrigin("localhost:8090")
            ->setSecure(false)
            ->setPublishKey('demo')
            ->setSubscribeKey('demo')
            ->setUserId('demo');
        $this->pubnub = new PubNub($this->config);
    }

    /**
     * @Given the demo keyset with enabled storage
     */
    public function theDemoKeysetWithEnabledStorage()
    {
        $this->config->setOrigin("localhost:8090")
            ->setSecure(false)
            ->setPublishKey('demo')
            ->setSubscribeKey('demo')
            ->setUserId('demo');
        $this->pubnub = new PubNub($this->config);
    }

    /**
     * @When I fetch message history for :channelName channel
     */
    public function iFetchMessageHistoryForChannel($channelName)
    {
        $this->channelName = $channelName;
        try {
            $this->response = $this->pubnub->fetchMessages()
                ->channels($this->channelName)
                ->sync();
        } catch (PubNubServerException $e) {
            $this->response = $e;
        }
    }

    /**
     * @When I fetch message history with :attribute set to :value for :channelName channel
     */
    public function iFetchMessageHistoryWithSetToForChannel($attribute, $value, $channelName)
    {
        $this->channelName = $channelName;
        $builder = $this->pubnub->fetchMessages()->channels($this->channelName);
        if ($attribute === "include_custom_message_type") {
            $builder->includeCustomMessageType($value === "true" ? true : false);
        }
        try {
            $this->response = $builder->sync();
        } catch (PubNubServerException $e) {
            $this->response = $e;
        }
    }

    /**
     * @Then history response contains messages with :messageType1 and :messageType2 message types
     */
    public function historyResponseContainsMessagesWithAndMessageTypes($messageType1, $messageType2)
    {
        $messages = $this->response->getChannels()[$this->channelName];
        assert((int)$messages[0]->getMessageType() === (int)$messageType1);
        assert((int)$messages[1]->getMessageType() === (int)$messageType2);
    }

    /**
     * @Then history response contains messages with :customMessageType1 and :customMessageType2 types
     */
    public function historyResponseContainsMessagesWithAndTypes($customMessageType1, $customMessageType2)
    {
        $messages = $this->response->getChannels()[$this->channelName];
        assert($messages[0]->getCustomMessageType() === $customMessageType1);
        assert($messages[1]->getCustomMessageType() === $customMessageType2);
    }

    /**
     * @Then history response contains messages without customMessageType
     */
    public function historyResponseContainsMessagesWithoutCustommessagetype()
    {
        foreach ($this->response->getChannels()[$this->channelName] as $message) {
            assert(is_null($message->getCustomMessageType()));
        }
    }

    /**
     * @When I publish message with :customMessageType customMessageType
     */
    public function iPublishMessageWithCustommessagetype($customMessageType)
    {
        try {
            $this->response = $this->pubnub->publish()
                ->channel("ch")
                ->message("msg")
                ->customMessageType($customMessageType)
                ->sync();
        } catch (PubNubServerException $e) {
            $this->response = $e;
        }
    }

    /**
     * @Then I receive a successful response
     */
    public function iReceiveASuccessfulResponse()
    {
        assert($this->response instanceof PNPublishResult || $this->response instanceof PNSignalResult
            || $this->response instanceof PNFetchMessagesResult);
    }

    /**
     * @Then I receive an error response
     */
    public function iReceiveAnErrorResponse()
    {
        assert($this->response instanceof PubNubServerException);
    }

    /**
     * @When I send a signal with :customMessageType customMessageType
     */
    public function iSendASignalWithCustommessagetype($customMessageType)
    {
        try {
            $this->response = $this->pubnub->signal()
                ->channel("ch")
                ->message("msg")
                ->customMessageType($customMessageType)
                ->sync();
        } catch (PubNubServerException $e) {
            $this->response = $e;
        }
    }
}
