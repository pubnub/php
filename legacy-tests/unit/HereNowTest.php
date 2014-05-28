<?php

require_once 'TestCase.php';


class HereNowTest extends TestCase
{
    protected static $message = 'Hello from here_now() test';
    protected static $channel = 'pubnub_php_test';

    public function testHereNow()
    {
        $response = $this->pubnub->here_now(array(
            'channel' => self::$channel,
        ));

        $this->assertEquals('200', $response['status']);
        $this->assertEquals('Presence', $response['service']);
    }

    public function testHereNowEmptyChannel()
    {
        $response = $this->pubnub->here_now(array(
            'channel' => '',
        ));

        $this->assertEquals(false, $response);
    }
}
 