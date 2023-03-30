<?php

namespace PubNubTests\Acceptance\Context\History;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use PubNub\Models\Consumer\History\PNHistoryResult;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTests\Acceptance\Context\PubNubContext;

class HistoryContext extends PubNubContext implements Context
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
     * @When I fetch message history for single channel
     */
    public function iFetchMessageHistoryForSingleChannel()
    {
        $this->context = $this->pubnub->history()
            ->channel('test')
            ->sync();
    }

    /**
     * @Then I receive successful response
     */
    public function iReceiveSuccessfulResponse()
    {
        assert($this->context instanceof PNHistoryResult);
    }

    /**
     * @Then the response contains pagination info
     */
    public function theResponseContainsPaginationInfo()
    {
        $doesSDKSupportPagination = false;
        assert($doesSDKSupportPagination == false);
    }

    /**
     * @When I fetch message history for multiple channels
     */
    public function iFetchMessageHistoryForMultipleChannels()
    {
        // feature not implemented in SDK
        throw new PendingException();
    }

    /**
     * @When I fetch message history with message actions
     */
    public function iFetchMessageHistoryWithMessageActions()
    {
        // feature not implemented in SDK
        throw new PendingException();
    }
}
