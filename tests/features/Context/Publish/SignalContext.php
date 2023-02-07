<?php

namespace PubNubTests\features\Context\Publish;

use Behat\Behat\Context\Context;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\PNMessageType;
use PubNub\Models\Consumer\PubSub\PNSignalResult;
use PubNubTests\Features\Context\PubNubContext;

class SignalContext extends PubNubContext implements Context
{
    use Traits\Given;
    use Traits\Then;

    /**
     * @When I send a signal with :spaceId space id and :messageType message type
     */
    public function iSendASignalWithSpaceIdAndMessageType($spaceId, $messageType)
    {
        try {
            $this->context = $this->pubnub->signal()
                ->message('test')
                ->channel('test')
                ->spaceId($spaceId)
                ->messageType(new PNMessageType($messageType))
                ->sync();
        } catch (PubNubServerException $exception) {
            $this->context = $exception;
        }
    }

    /**
     * @Then I receive a successful response
     */
    public function iReceiveASuccessfulResponse()
    {
        assert($this->context instanceof PNSignalResult);
    }
}
