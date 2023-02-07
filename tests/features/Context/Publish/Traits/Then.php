<?php

namespace PubNubTests\Features\Context\Publish\Traits;

use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\PNPublishResult;

trait Then
{
    /**
     * @Then I receive an error response
     */
    public function iReceiveAnErrorResponse()
    {
        assert($this->context instanceof PubNubServerException);
    }
}
