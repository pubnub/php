<?php

namespace PubNubTests\Acceptance\Context\Signal;

use Behat\Behat\Context\Context;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\PubSub\PNSignalResult;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTests\Acceptance\Context\PubNubContext;

class SignalContext extends PubNubContext implements Context
{
    /**
     * @Given the demo keyset
     */
    public function theDemoKeyset()
    {
        $this->pnConfig = PNConfiguration::demoKeys();
        $this->pubnub = new PubNub($this->pnConfig);
    }

    /**
     * @When I send a signal with :spaceId space id and :type type
     */
    public function iSendASignalWithSpaceIdAndType($spaceId, $type)
    {
        try {
            $this->context = $this->pubnub->signal()
                ->message('test')
                ->channel('test')
                ->spaceId($spaceId)
                ->type($type)
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

    /**
     * @Then I receive an error response
     */
    public function iReceiveAnErrorResponse()
    {
        assert($this->context instanceof PubNubServerException);
    }
}
