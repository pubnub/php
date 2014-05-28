<?php

require_once 'TestCase.php';


class PublishJSONTest extends TestCase {

    protected $pubnub_enc;
    protected static $messagea = array('Hello from publishString() ', 'test');
    protected static $channel = 'pubnub_php_test';

    public function testPublishJSON()
    {
        $response = $this->pubnub->publish(array(
            'channel' => self::$channel,
            'message' => self::$messagea
        ));

        $this->assertEquals(1, $response[0]);
        $this->assertEquals('Sent', $response[1]);
        $this->assertGreaterThan(1400688897 * 10000000, $response[2]);
    }
}
 