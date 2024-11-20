<?php

namespace PubNubTests\Acceptance\Subscribe;

use Behat\Behat\Context\Context;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\Server\MessageType;
use PubNubTests\Acceptance\PubNubContext;
use PubNub\PubNub;
use PubNub\PNConfiguration;

/**
 * Defines application features from the specific context.
 */
class SubscribeContext extends PubNubContext implements Context
{
    private PubNub $pubnub;
    private PNConfiguration $config;
    private string $channelName;
    /** @var PNMessageResult[] */
    private array $messageResults = [];

    public function __construct()
    {
        $this->config = new PNConfiguration();
    }

    public function addMessage(PNMessageResult $message): void
    {
        $this->messageResults[] = $message;
    }

    /**
     * @Given the demo keyset
     */
    public function theDemoKeyset(): void
    {
        $this->config->setOrigin("localhost:8090")
            ->setSecure(false)
            ->setPublishKey('demo')
            ->setSubscribeKey('demo')
            ->setUserId('demo')
            ->setSubscribeTimeout(1);
        $this->pubnub = new PubNub($this->config);
    }

    /**
     * @When I subscribe to :channelName channel
     */
    public function iSubscribeToChannel(string $channelName): void
    {
        $callback = new AcceptanceTestSubscribeCallback($this);
        $this->pubnub->addListener($callback);
        $this->channelName = $channelName;
        $this->pubnub->subscribe()->channels($this->channelName)->execute();
    }

    /**
     * @Then I receive :numberOf messages in my subscribe response
     */
    public function iReceiveMessagesInMySubscribeResponse(string $numberOf): void
    {
        assert(count($this->messageResults) === (int)$numberOf);
    }

    /**
     * @Then response contains messages with :firstCustomType and :secondCustomType types
     */
    public function responseContainsMessagesWithAndTypes(string $firstCustomType, string $secondCustomType): void
    {
        assert($this->messageResults[0]->getCustomMessageType() === $firstCustomType);
        assert($this->messageResults[1]->getCustomMessageType() === $secondCustomType);
    }
}
