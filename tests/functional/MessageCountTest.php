<?php
namespace Tests\Functional;

use PubNub\Endpoints\MessageCount;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use Tests\Helpers\StubTransport;


class MessageCountTest extends \PubNubTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
    }

    public function testSyncDisabled()
    {
        $this->expectException(PubNubServerException::class);

        $messageCount = new MessageCountExposed($this->pubnub);

        $payload = "[\"Use of the history API requires the Storage & Playback which is not enabled for this " .
            "subscribe key.Login to your PubNub Dashboard Account and enable Storage & Playback.Contact support " .
            "@pubnub.com if you require further assistance.\",0,0]";

        $messageCount->stubFor("/v3/history/sub-key/". parent::SUBSCRIBE_KEY . "/message-counts/my_channel")
            ->withQuery([
                "timetoken" => "10000",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
            ])
            ->setResponseBody($payload);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $messageCount->channels(["my_channel"])
            ->timetoken("10000")->sync();

    }

    public function testSingleChannel_withSingleTimestamp()
    {
        $messageCount = new MessageCountExposed($this->pubnub);

        $payload = "{\"status\": 200, \"error\": false, \"error_message\": \"\", " .
            "\"channels\": {\"my_channel\":19}}";

        $messageCount->stubFor("/v3/history/sub-key/". parent::SUBSCRIBE_KEY . "/message-counts/my_channel")
            ->withQuery([
                "timetoken" => "10000",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
            ])
            ->setResponseBody($payload);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $messageCount->channels(["my_channel"])
            ->timetoken("10000")->sync();

        $this->assertEquals(count($response->getChannels()), 1);
        $this->assertFalse(isset($response->getChannels()["channel_dont_exist"]));
        $this->assertTrue(isset($response->getChannels()["my_channel"]));
        foreach($response->getChannels() as $channel => $count) {
            $this->assertEquals("my_channel", $channel);
            $this->assertEquals(19, $count);
        }

    }

    public function testSingleChannel_withMultiTimestamp()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("The number of channels and the number of timetokens do not match");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->channels(["my_channel"])
            ->channelsTimetoken(["10000", "20000"])->sync();

    }

    public function testMultiChannel_withSingleTimestamp()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("The number of channels and the number of timetokens do not match");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->channels(["my_channel","new_channel"])
            ->channelsTimetoken(["10000"])->sync();

    }

    public function testMultiChannel_withMultiTimestamp()
    {
        $messageCount = new MessageCountExposed($this->pubnub);

        $payload = "{\"status\": 200, \"error\": false, \"error_message\": \"\", " .
    "\"channels\": {\"my_channel\":19, \"new_channel\":5}}";

        $messageCount->stubFor("/v3/history/sub-key/". parent::SUBSCRIBE_KEY . "/message-counts/my_channel,new_channel")
            ->withQuery([
                "channelsTimetoken" => PubNubUtil::joinitems(["10000", "20000"]),
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
            ])
            ->setResponseBody($payload);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $messageCount->channels(["my_channel","new_channel"])
            ->channelsTimetoken(["10000", "20000"])->sync();

        $this->assertEquals(count($response->getChannels()), 2);
        $this->assertFalse(isset($response->getChannels()["channel_dont_exist"]));
        $this->assertTrue(isset($response->getChannels()["my_channel"]));
        $this->assertTrue(isset($response->getChannels()["new_channel"]));
        foreach($response->getChannels() as $channel => $count) {
            if($channel === "my_channel") {
                $this->assertEquals(19, $count);
            }
            elseif($channel === "new_channel") {
                $this->assertEquals(5, $count);
            }
        }

    }


    public function testWithoutTimeToken()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Timetoken missing");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->channels(["my_channel"])->sync();

    }

    public function testWithoutChannels_SingleTimeToken()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->timetoken("10000")->sync();

    }

    public function testWithoutChannels_TimeTokenList()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->channelsTimetoken(["10000", "20000"])->sync();

    }

    public function testSingleChannel_SingleTimeTokenAndList()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("timetoken and channelTimetokens are incompatible together");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->channels(["my_channel"])
            ->channelsTimetoken(["10000", "20000"])
            ->timetoken("10000")->sync();

    }

    public function testChannel_withSingleEmptyToken()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Timetoken missing");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->stubFor("/v3/history/sub-key/". parent::SUBSCRIBE_KEY . "/message-counts/my_channel")
            ->withQuery([
                "timetoken" => null,
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
            ]);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $messageCount->channels(["my_channel"])
            ->timetoken(null)->sync();

    }

    public function testChannel_withMultiEmptyToken()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Timetoken missing");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->stubFor("/v3/history/sub-key/". parent::SUBSCRIBE_KEY . "/message-counts/my_channel")
            ->withQuery([
                "channelsToken" => [],
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
            ]);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $messageCount->channels(["my_channel"])
            ->channelsTimetoken([])->sync();

    }

    public function testChannel_withMultiNullToken()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Timetoken missing");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->stubFor("/v3/history/sub-key/". parent::SUBSCRIBE_KEY . "/message-counts/my_channel")
            ->withQuery([
                "channelsToken" => null,
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
            ]);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $messageCount->channels(["my_channel"])
            ->channelsTimetoken(null)->sync();

    }

}

class MessageCountExposed extends MessageCount
{
    protected $transport;

    public function __construct(PubNub $pubnubInstance)
    {
        parent::__construct($pubnubInstance);

        $this->transport = new StubTransport();
    }

    public function stubFor($url)
    {
        return $this->transport->stubFor($url);
    }

    public function requestOptions()
    {
        return [
            'transport' => $this->transport
        ];
    }

}