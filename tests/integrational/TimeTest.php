<?php

//use PHPUnit\Framework\TestCase;


class TimeTest extends TestCase
{
    protected static $channel = 'pubnub_php_test';

    /**
     * @group herenow
     */
    public function testHereNow()
    {
        $response = $this->pubnub->time()->sync();

        $this->assertGreaterThan(14838270462685247, $response->getTimetoken());
    }
}
