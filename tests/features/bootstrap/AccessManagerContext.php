<?php

use Behat\Behat\Context\Context;
use PubNub\PNConfiguration;
use PubNubFeatures\PubNubContext;
use PubNubFeatures\Access as AccessTraits;

/**
 * Defines application features from the specific context.
 */
class AccessManagerContext extends PubNubContext implements Context
{
    use AccessTraits\Given;
    use AccessTraits\When;
    use AccessTraits\Then;

    private $origin = 'localhost:8090';
    private $pnConfig;
    private $pubnub;
    private $context = [];
    private $resource = null;
    private $pattern = null;
    private $token;

    public function __construct()
    {
        $this->pnConfig = new PNConfiguration();
        $this->pnConfig->setPublishKey('pub-c-mock-key');
        $this->pnConfig->setSubscribeKey('sub-c-mock-key');
        $this->pnConfig->setSecretKey('sec-c-mock-key');
        $this->pnConfig->setOrigin($this->origin);
        $this->pnConfig->setSecure(false);
    }
}
