<?php

namespace Tests\Integrational;


class TimeTest extends \PubNubTestCase
{
    /**
     * @group time
     * @group time-integrational
     */
    public function testTime()
    {
        $response = $this->pubnub->time()->sync();

        $this->assertGreaterThan(14838270462685247, $response->getTimetoken());
    }
}
