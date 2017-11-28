<?php

namespace Tests\Functional;

use PubNub\Managers\TelemetryManager;
use PubNub\Enums\PNOperationType;


class TelemetryManagerTest extends \PubNubTestCase
{
    public function testCleanUpTest()
    {
        $manager = new TelemetryManagerExposed();

        for ($i = 0; $i < 10; $i++) {
            $manager->storeLatency($i, PNOperationType::PNPublishOperation);
        }

        // await for store timestamp expired
        sleep(2);

        $manager->cleanUpTelemetryData();

        $this->assertEquals(0, count($manager->operationLatencies()));
    }
}


class TelemetryManagerExposed extends TelemetryManager
{
    const MAXIMUM_LATENCY_DATA_AGE = 1;
}