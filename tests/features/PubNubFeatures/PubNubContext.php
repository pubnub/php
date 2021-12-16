<?php

namespace PubNubFeatures;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class PubNubContext
{
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
