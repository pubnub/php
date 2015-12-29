<?php

use Pubnub\Pubnub;
use \Pubnub\PubnubException;

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
        $this->pubnub->publish($this->channel, static::$message.$m1);
        $this->pubnub->publish($this->channel, static::$message_2 . $m2);

        sleep(3);

        $response = $this->pubnub->history($this->channel, 2);

        $this->assertEquals(static::$message . $m1, $response['messages'][count($response['messages']) - 2]);
        $this->assertEquals(static::$message_2 . $m2, $response['messages'][count($response['messages']) - 1]);
    }

    /**
     * @group history
     */
    public function testHistoryMessagesMultipleLevel()
    {
        $m1 = static::$message . time();
        $m2 = static::$message_2 . time();
        $m3 = static::$message . time();
        $m4 = static::$message_2 . time();

        $ary1 = array(
            'first' => $m1,
            'second' => $m2
        );

        $ary2 = array(
            'third' => $m3,
            'fourth' => $m4
        );

        $this->pubnub->publish($this->channel, $ary1);
        $this->pubnub->publish($this->channel, $ary2);

        sleep(1);

        $response = $this->pubnub->history($this->channel,2);

        $this->assertEquals($ary1, $response['messages'][count($response['messages']) - 2]);
        $this->assertEquals($ary2, $response['messages'][count($response['messages']) - 1]);
    }

    /**
     * @group history
     */
    public function testHistoryEncodedMessagesOneLevel()
    {
        $pubnub = new Pubnub(array(
            'publish_key' => 'demo',
            'subscribe_key' => 'demo',
            'cipher_key' => 'blah'
        ));

        $m1 = time();
        $m2 = time();
        $pubnub->publish($this->channel, static::$message . $m1);
        $pubnub->publish($this->channel, static::$message_2 . $m2);

        sleep(1);

        $response = $pubnub->history($this->channel,2);

        $this->assertEquals(static::$message . $m1, $response['messages'][count($response['messages']) - 2]);
        $this->assertEquals(static::$message_2 . $m2, $response['messages'][count($response['messages']) - 1]);
    }

    /**
     * @group history
     */
    public function testHistoryEncodedMessagesMultipleLevel()
    {
        $pubnub = new Pubnub(array(
            'publish_key' => 'demo',
            'subscribe_key' => 'demo',
            'cipher_key' => 'blah'
        ));

        $m1 = static::$message . time();
        $m2 = static::$message_2 . time();
        $m3 = static::$message . time();
        $m4 = static::$message_2 . time();

        $ary1 = array(
            'first' => $m1,
            'second' => $m2
        );

        $ary2 = array(
            'third' => $m3,
            'fourth' => $m4
        );

        $pubnub->publish($this->channel, $ary1);
        $pubnub->publish($this->channel, $ary2);

        sleep(1);

        $response = $pubnub->history($this->channel,2);

        $this->assertEquals($ary1, $response['messages'][count($response['messages']) - 2]);
        $this->assertEquals($ary2, $response['messages'][count($response['messages']) - 1]);
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
        $start = time() * 10000000;
        $channel = $this->channel . time();

        $this->pubnub->publish($channel, static::$message . "#1");
        $this->pubnub->publish($channel, static::$message . "#2");
        $this->pubnub->publish($channel, static::$message . "#3");
        $this->pubnub->publish($channel, static::$message . "#4");
        $this->pubnub->publish($channel, static::$message . "#5");

        sleep(3);

        $response = $this->pubnub->history($channel, 3, 1, $start, null, true);
        $this->assertEquals(static::$message . '#1', (string) $response['messages']['0']['message']);

        $response = $this->pubnub->history($channel, 3, 1, null, $start/* as end */, false);
        $this->assertEquals(static::$message . '#3', (string) $response['messages']['0']['message']);
    }

    /**
     * @group history
     */
    public function testHistoryInvalidChannel()
    {
        $this->setExpectedException('\Pubnub\PubnubException');
        $this->pubnub->history('');
    }

    /**
     * @group history
     */
    public function testHistoryErrorWhenServiceIsDisabled()
    {
        $pubnub = new Pubnub(array(
            'publish_key' => 'pub-c-2123c3b4-7435-4365-b05f-d57f1746a4de',
            'subscribe_key' => 'sub-c-458ba4d6-0536-11e5-aefa-0619f8945a4f'));

        $result = $pubnub->history('channelName');

        $this->assertEquals(1, $result['error']);
        $this->assertEquals('storage', $result['service']);
        $this->assertEquals('Storage is not enabled for this subscribe key. Please contact help@pubnub.com', $result['message']);
    }

    /**
     * @group history
     */
    public function testHistoryErrorWithWrongKeys()
    {
        $pubnub = new Pubnub(array(
            'publish_key' => 'asdf',
            'subscribe_key' => 'qwer',
        ));

        $result = $pubnub->history('channelName');

        $this->assertEquals(400, $result['status']);
        $this->assertEquals(1, $result['error']);
        $this->assertEquals('Invalid Subscribe Key', $result['message']);
    }
}
