<?php

use Behat\Behat\Context\Context;
use PubNubFeatures\PubNubContext;

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
        return ('PubNub\Models\Consumer\PNTimeResult' === get_class($this->response));
    }
}
