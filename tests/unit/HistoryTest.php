<?php

use Pubnub\Pubnub;

class HistoryTest extends TestCase
{
    protected $channel = 'pubnub_php_test_history';
    protected $bootstrap;
    protected $start;
    protected static $message = 'Hello from history() test!';
    protected static $message_2 = 'Hello from history() test! 2';

    public function setUp()
    {
        parent::setUp();

        $this->start = $this->pubnub->time();
        $bootstrap = time();

        for ($i = 0; $i < 10; $i++) {
            $this->pubnub->publish(array(
                'channel' => $this->channel,
                'message' => "${bootstrap}#${i}"
            ));
        }
    }

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

        sleep(3);

        $response = $this->pubnub->history(array(
            'channel' => $this->channel,
            'count' => 2
        ));

        $this->assertCount(2, $response['messages']);
        $this->assertEquals(static::$message, $response['messages'][0]);
        $this->assertEquals(static::$message_2, $response['messages'][1]);
    }

    public function testHistoryIncludeToken()
    {
        $response = $this->pubnub->history(array(
            'channel' => $this->channel,
            'count' => 2,
            'include_token' => true
        ));

        $this->assertArrayHasKey('message', $response['messages'][0]);
        $this->assertArrayHasKey('timetoken', $response['messages'][0]);
    }

    public function testHistoryReverse()
    {
        $response = $this->pubnub->history(array(
            'channel' => $this->channel,
            'count' => 12,
            'start' => $this->start,
            'reverse' => true
        ));

        $this->assertRegExp('/#0$/', $response['messages'][0]);
    }

    public function testHistoryInvalidChannel()
    {
        $response = $this->pubnub->history(array(
            'channel' => ""
        ));

        $this->assertEquals(false, $response);
    }
}
