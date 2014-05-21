<?php

use Pubnub\Pubnub;

class HistoryTest extends TestCase
{
    protected $channel = 'pubnub_php_test_history';
    protected static $message = 'Hello from history() test!';
    protected static $message_2 = 'Hello from history() test! 2';

    public function testHistoryMessages()
    {
        $this->pubnub->publish(array(
            'channel' => $this->channel,
            'message' => static::$message
        ));

        $this->pubnub->publish(array(
            'channel' => $this->channel,
            'message' => static::$message_2
        ));

        $response = $this->pubnub->history(array(
            'channel' => $this->channel,
            'count' => 2
        ));

        $this->assertCount(2, $response['messages']);
        $this->assertEquals($response['messages'][0], static::$message);
        $this->assertEquals($response['messages'][1], static::$message_2);
    }

    public function testHistoryInvalidChannel()
    {
        $response = $this->pubnub->history(array(
            'channel' => ""
        ));

        $this->assertEquals(false, $response);
    }
}
