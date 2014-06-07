<?php

require_once 'TestCase.php';

class HereNowTest extends TestCase
{
    protected static $message = 'Hello from here_now() test';
    protected static $channel = 'pubnub_php_test';

    /**
     * @group herenow
     */
    public function testHereNow()
    {
        $response = $this->pubnub->hereNow(self::$channel);
        $this->assertEquals('200', $response['status']);
        $this->assertEquals('Presence', $response['service']);
    }

    /**
     * @group herenow
     */
    public function testHereNowEmptyChannel()
    {
        try {
            $this->pubnub->hereNow('');
            $this->fail("exception was not thrown");
        } catch (Exception $e) {
            $this->assertEquals('Missing Channel in hereNow()', $e->getMessage());
        }
    }
}
 