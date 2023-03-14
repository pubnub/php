<?php

namespace PubNubTests\Acceptance\Context\Utils;

use Behat\Behat\Context\Context;
use PubNubTests\Acceptance\Context\PubNubContext;
use PubNub\Models\Consumer\PNTimeResult;

/**
 * Defines application features from the specific context.
 */
class TimeContext extends PubNubContext implements Context
{
    private $response;
    /**
     * @When I request current time
     */
    public function iRequestCurrentTime()
    {
        $pnconfig = \PubNub\PNConfiguration::demoKeys();
        $pubnub = new \PubNub\PubNub($pnconfig);

        $this->response = $pubnub->time()->sync();
        return true;
    }

    /**
     * @Then I receive successful response
     */
    public function iReceiveSuccessfulResponse()
    {
        return (PNTimeResult::class === get_class($this->response));
    }
}
