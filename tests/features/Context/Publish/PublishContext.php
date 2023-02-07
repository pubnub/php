<?php

namespace PubNubTests\features\Context\Publish;

use Behat\Behat\Context\Context;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\PNMessageType;
use PubNub\Models\Consumer\PNPublishResult;
use PubNubTests\Features\Context\PubNubContext;

class PublishContext extends PubNubContext implements Context
{
    use Traits\Given;
    use Traits\Then;

    /**
     * @When I publish message with :spaceId space id and :messageType message type
     */
    public function iPublishMessageWithSpaceIdAndMessageType($spaceId, $messageType)
    {
        try {
            $this->context = $this->pubnub->publish()
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
        assert($this->context instanceof PNPublishResult);
    }

    /**
     * @Then I receive an error response
     */
    public function iReceiveAnErrorResponse()
    {
        assert($this->context instanceof PubNubServerException);
    }
}
