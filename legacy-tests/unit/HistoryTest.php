<?php

require_once 'TestCase.php';

class HistoryTest extends TestCase
{
    protected $channel = 'pubnub_php_test_history';
    protected $bootstrap;
    protected $start = 5;
    protected static $message = 'Hello from history() test!';
    protected static $message_2 = 'Hello from history() test! 2';

    public function setUp()
    {
        parent::setUp();

        $this->start = $this->pubnub->time();
    }

    /**
     * @group history
     */
    public function testHistoryMessages()
    {
        $m1 = time();
        $m2 = time();
        $this->pubnub->publish($this->channel, self::$message.$m1);
        $this->pubnub->publish($this->channel, self::$message_2.$m2);

        sleep(3);

        $response = $this->pubnub->history($this->channel,2);

        $this->assertEquals(self::$message.$m1, $response['messages'][count($response['messages']) - 2]);
        $this->assertEquals(self::$message_2.$m2, $response['messages'][count($response['messages']) - 1]);
    }

    /**
     * @group history
     */
    public function testHistoryIncludeToken()
    {
        $response = $this->pubnub->history($this->channel, 2, true);

        $this->assertArrayHasKey('message', $response['messages'][0]);
        $this->assertArrayHasKey('timetoken', $response['messages'][0]);
    }

    /**
     * @group history
     */
    public function testHistoryReverse()
    {
        $response = $this->pubnub->history($this->channel, 10, 1, null, null, true);
        $this->assertRegExp('/[0-9]+$/', (string)$response['messages'][0]['message']);
    }

    /**
     * @group history
     */
    public function testHistoryInvalidChannel()
    {
        try {
            $this->pubnub->history('');
            $this->fail("exception was not thrown");
        } catch (Exception $e) {
            $this->assertEquals('Missing Channel in history()', $e->getMessage());
        }
    }
}
