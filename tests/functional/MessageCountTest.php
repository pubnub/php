<?php

namespace Tests\Functional;

use PubNub\Endpoints\Endpoint;
use PubNub\Endpoints\MessageCount;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class MessageCountTest extends \PubNubTestCase
{
    protected Endpoint $endpoint;

    public function testSyncDisabled()
    {
        $this->expectException(PubNubServerException::class);

        $messageCount = new MessageCountExposed($this->pubnub);

        $payload = "[\"Use of the history API requires the Storage & Playback which is not enabled for this " .
            "subscribe key.Login to your PubNub Dashboard Account and enable Storage & Playback.Contact support " .
            "@pubnub.com if you require further assistance.\",0,0]";

        $messageCount->stubFor(
            "/v3/history/sub-key/" . $this->pubnub->getConfiguration()->getSubscribeKey() . "/message-counts/my_channel"
        )->withQuery([
                "timetoken" => "10000",
                "pnsdk" => $this->pubnub->getSdkFullName(),
                "uuid" => "myUUID",
            ])
            ->setResponseBody($payload);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $messageCount->channels(["my_channel"])->channelsTimetoken(["10000"])->sync();
    }

    public function testSingleChannelWithSingleTimestamp()
    {
        $messageCount = new MessageCountExposed($this->pubnub);

        $payload = "{\"status\": 200, \"error\": false, \"error_message\": \"\", " .
            "\"channels\": {\"my_channel\":19}}";

        $messageCount->stubFor(
            "/v3/history/sub-key/" . $this->pubnub->getConfiguration()->getSubscribeKey() . "/message-counts/my_channel"
        )->withQuery([
                "timetoken" => "10000",
                "pnsdk" => $this->pubnub->getSdkFullName(),
                "uuid" => "myUUID",
            ])
            ->setResponseBody($payload);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $messageCount->channels(["my_channel"])
            ->channelsTimetoken(["10000"])->sync();

        $this->assertEquals(count($response->getChannels()), 1);
        $this->assertFalse(isset($response->getChannels()["channel_dont_exist"]));
        $this->assertTrue(isset($response->getChannels()["my_channel"]));
        foreach ($response->getChannels() as $channel => $count) {
            $this->assertEquals("my_channel", $channel);
            $this->assertEquals(19, $count);
        }
    }

    public function testSingleChannelWithMultiTimestamp()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("The number of channels and the number of timetokens do not match");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->channels(["my_channel"])
            ->channelsTimetoken(["10000", "20000"])->sync();
    }

    public function testMultiChannelWithSingleTimestamp()
    {
        $messageCount = new MessageCountExposed($this->pubnub);

        $payload = "{\"status\": 200, \"error\": false, \"error_message\": \"\", " .
            "\"channels\": {\"my_channel\":19, \"new_channel\":5}}";

        $messageCount->stubFor(
            "/v3/history/sub-key/" . $this->pubnub->getConfiguration()->getSubscribeKey()
            . "/message-counts/my_channel,new_channel"
        )->withQuery([
                "timetoken" => "10000",
                "pnsdk" => $this->pubnub->getSdkFullName(),
                "uuid" => "myUUID",
            ])
            ->setResponseBody($payload);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $messageCount->channels(["my_channel","new_channel"])
            ->channelsTimetoken(["10000"])->sync();

        $this->assertEquals(count($response->getChannels()), 2);
        $this->assertFalse(isset($response->getChannels()["channel_dont_exist"]));
        $this->assertTrue(isset($response->getChannels()["my_channel"]));
        $this->assertTrue(isset($response->getChannels()["new_channel"]));
        foreach ($response->getChannels() as $channel => $count) {
            if ($channel === "my_channel") {
                $this->assertEquals(19, $count);
            } elseif ($channel === "new_channel") {
                $this->assertEquals(5, $count);
            }
        }
    }

    public function testMultiChannelWithMultiTimestamp()
    {
        $messageCount = new MessageCountExposed($this->pubnub);

        $payload = "{\"status\": 200, \"error\": false, \"error_message\": \"\", " .
            "\"channels\": {\"my_channel\":19, \"new_channel\":5}}";

        $messageCount->stubFor(
            "/v3/history/sub-key/" . $this->pubnub->getConfiguration()->getSubscribeKey()
            . "/message-counts/my_channel,new_channel"
        )->withQuery([
                "channelsTimetoken" => PubNubUtil::joinitems(["10000", "20000"]),
                "pnsdk" => $this->pubnub->getSdkFullName(),
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
        foreach ($response->getChannels() as $channel => $count) {
            if ($channel === "my_channel") {
                $this->assertEquals(19, $count);
            } elseif ($channel === "new_channel") {
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

    public function testWithoutChannelsSingleTimeToken()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->channelsTimetoken(["10000"])->sync();
    }

    public function testWithoutChannelsTimeTokenList()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->channelsTimetoken(["10000", "20000"])->sync();
    }

    public function testChannelWithMultiEmptyToken()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Timetoken missing");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->stubFor(
            "/v3/history/sub-key/" . $this->pubnub->getConfiguration()->getSubscribeKey() . "/message-counts/my_channel"
        )->withQuery([
                "channelsToken" => PubNubUtil::joinitems([]),
                "pnsdk" => $this->pubnub->getSdkFullName(),
                "uuid" => "myUUID",
            ]);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $messageCount->channels(["my_channel"])
            ->channelsTimetoken([])->sync();
    }

    public function testChannelWithMultiNullToken()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Timetoken missing");

        $messageCount = new MessageCountExposed($this->pubnub);

        $messageCount->stubFor(
            "/v3/history/sub-key/" . $this->pubnub->getConfiguration()->getSubscribeKey() . "/message-counts/my_channel"
        )->withQuery([
                "timetoken" => null,
                "pnsdk" => $this->pubnub->getSdkFullName(),
                "uuid" => "myUUID",
            ]);

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $messageCount->channels(["my_channel"])
            ->channelsTimetoken(null)->sync();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class MessageCountExposed extends MessageCount
{
    protected $client;

    public function __construct(PubNub $pubnubInstance)
    {
        parent::__construct($pubnubInstance);
        $this->client = new PsrStubClient();
        $pubnubInstance->setClient($this->client);
    }

    public function stubFor($url)
    {
        $stub = new PsrStub($url);
        $this->client->addStub($stub);
        return $stub;
    }
}
