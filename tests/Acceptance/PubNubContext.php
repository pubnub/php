<?php

namespace PubNubTests\Acceptance;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class PubNubContext
{
    protected int $retryLimit = 12;

    /** @BeforeScenario */
    public function before(BeforeScenarioScope $scope): void
    {
        $this->waitForServer();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

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
    public function after(AfterScenarioScope $scope): void
    {
        $this->waitForServer();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        foreach ($scope->getScenario()->getTags() as $tag) {
            if (0 === strpos($tag, 'contract')) {
                curl_setopt($ch, CURLOPT_URL, 'http://localhost:8090/expect');
                curl_exec($ch);
            }
        }
        curl_close($ch);
    }

    protected function waitForServer()
    {

        for ($i = 1; $i <= $this->retryLimit; $i++) {
            print("Trying to connect ($i/$this->retryLimit)...\n");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:8090');
            curl_exec($ch);
            $err = curl_error($ch);
            if ($err === "") {
                print("Server started\n");
                break;
            }
            print($err . "\n");

            if ($i === $this->retryLimit) {
                print("Server not started\n");
                exit(1);
            }
            sleep($i);
        }
    }
}
