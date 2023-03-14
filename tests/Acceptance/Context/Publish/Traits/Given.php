<?php

namespace PubNubTests\Acceptance\Context\Publish\Traits;

use PubNub\PNConfiguration;
use PubNub\PubNub;

trait Given
{
    /**
     * @Given the demo keyset
     */
    public function theDemoKeyset()
    {
        $this->pnConfig = PNConfiguration::demoKeys();
        $this->pubnub = new PubNub($this->pnConfig);
    }
}
