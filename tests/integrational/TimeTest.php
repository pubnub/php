<?php

//use PHPUnit\Framework\TestCase;


class TimeTest extends PubNubTestCase
{
    protected static $channel = 'pubnub_php_test';

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
