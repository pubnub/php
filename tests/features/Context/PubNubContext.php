<?php

namespace PubNubTests\Features\Context;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use PubNub\PNConfiguration;

class PubNubContext
{
    protected $origin = 'localhost:8090';
    protected $pnConfig;
    protected $pubnub;
    protected $context = [];

    public function __construct()
    {
        $this->pnConfig = new PNConfiguration();
        $this->pnConfig->setPublishKey('pub-c-mock-key');
        $this->pnConfig->setSubscribeKey('sub-c-mock-key');
        $this->pnConfig->setSecretKey('sec-c-mock-key');
        $this->pnConfig->setOrigin($this->origin);
        $this->pnConfig->setUserId('mock-user-id');
        $this->pnConfig->setSecure(false);
    }

    /** @BeforeScenario */
    public function before(BeforeScenarioScope $scope)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        foreach ($scope->getScenario()->getTags() as $tag) {
            if (0 === strpos($tag, 'contract')) {
                list(, $contractName) = explode('=', $tag);
                curl_setopt($ch, CURLOPT_URL, 'http://localhost:8090/init?__contract__script__=' . $contractName);
                curl_exec($ch);
            }
        }
        curl_close($ch);
    }

    /** @AfterScenario */
    public function after(AfterScenarioScope $scope)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        foreach ($scope->getScenario()->getTags() as $tag) {
            if (0 === strpos($tag, 'contract')) {
                curl_setopt($ch, CURLOPT_URL, 'http://localhost:8090/expect');
                curl_exec($ch);
            }
        }
        curl_close($ch);
    }
}
