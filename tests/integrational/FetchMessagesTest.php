<?php

namespace Tests\Integrational;

use PubNub\Endpoints\MessagePersistance\FetchMessages;
use PubNub\Models\Consumer\MessagePersistence\PNFetchMessagesResult;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\StubTransport;

class FetchMessagesTest extends PubNubTestCase
{
    protected const CHANNEL_NAME = 'TheMessageHistoryChannelHD';
    protected const ENCRYPTED_CHANNEL_NAME = 'TheMessageHistoryChannelHD-ENCRYPTED';


    protected const MESSAGE_COUNT = 10;

    protected $startTimetoken = null;
    protected $middleTimetoken = null;
    protected $endTimetoken = null;

    public function testFetchWithDefaults()
    {
        $subKey = $this->pubnub_demo->getConfiguration()->getSubscribeKey();
        $fetchMessages = new FetchMessagesExposed($this->pubnub_demo);

        $fetchMessages
            ->stubFor("/v3/history/sub-key/demo/channel/TheMessageHistoryChannelHD")
            ->withQuery([
                "uuid" => $this->pubnub_demo->getConfiguration()->getUserId(),
                "pnsdk" => $this->encodedSdkName,
                "include_meta" => "false",
                "include_uuid" => "false",
                "include_message_type" => "true",
                "include_custom_message_type" => "false",
            ])
            ->setResponseBody('{"status": 200, "error": false, "error_message": "", "channels":
                {"TheMessageHistoryChannelHD":[
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 1","timetoken":"17165627034260904"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 2","timetoken":"17165627036256425"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 3","timetoken":"17165627038256616"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 4","timetoken":"17165627040258555"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 5","timetoken":"17165627042258446"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 6","timetoken":"17165627044259064"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 7","timetoken":"17165627046254982"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 8","timetoken":"17165627048260069"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 9","timetoken":"17165627050260263"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 10","timetoken":"17165627052255699"}
                ]}}');

        $response = $fetchMessages->channels(self::CHANNEL_NAME)->sync();
        $this->assertInstanceOf(PNFetchMessagesResult::class, $response);

        $this->assertEquals(
            self::MESSAGE_COUNT,
            count($response->getChannels()[self::CHANNEL_NAME])
        );
    }

    public function testFetchWithCount()
    {
        $subKey = $this->pubnub_demo->getConfiguration()->getSubscribeKey();
        $fetchMessages = new FetchMessagesExposed($this->pubnub_demo);

        $fetchMessages
            ->stubFor("/v3/history/sub-key/{$subKey}/channel/TheMessageHistoryChannelHD")
            ->withQuery([
                "max" => "5",
                "uuid" => $this->pubnub_demo->getConfiguration()->getUserId(),
                "pnsdk" => $this->encodedSdkName,
                "include_meta" => "false",
                "include_uuid" => "false",
                "include_message_type" => "true",
                "include_custom_message_type" => "false",
            ])
            ->setResponseBody('{"status": 200, "error": false, "error_message": "", "channels":
                {"TheMessageHistoryChannelHD":[
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 6","timetoken":"17165627044259064"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 7","timetoken":"17165627046254982"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 8","timetoken":"17165627048260069"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 9","timetoken":"17165627050260263"},
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 10","timetoken":"17165627052255699"}
                ]}}');

        $response = $fetchMessages->channels(self::CHANNEL_NAME)
            ->count(5)
            ->sync();

        $this->assertInstanceOf(PNFetchMessagesResult::class, $response);

        $this->assertEquals(5, count($response->getChannels()[self::CHANNEL_NAME]));
    }

    public function testFetchWithStartEnd()
    {
        $subKey = $this->pubnub_demo->getConfiguration()->getSubscribeKey();
        $fetchMessages = new FetchMessagesExposed($this->pubnub_demo);

        $fetchMessages
            ->stubFor("/v3/history/sub-key/{$subKey}/channel/TheMessageHistoryChannelHD")
            ->withQuery([
                "start" => "17165627042258346",
                "end" => "17165627042258546",
                "uuid" => $this->pubnub_demo->getConfiguration()->getUserId(),
                "pnsdk" => $this->encodedSdkName,
                "include_meta" => "false",
                "include_uuid" => "false",
                "include_message_type" => "true",
                "include_custom_message_type" => "false",
            ])
            ->setResponseBody('{"status": 200, "error": false, "error_message": "", "channels":
                {"TheMessageHistoryChannelHD":[
                    {"message":"hello TheMessageHistoryChannelHD channel. Message: 5","timetoken":"17165627042258446"}
                ]}}');

        $response = $fetchMessages->channels(self::CHANNEL_NAME)
            ->start(17165627042258346)
            ->end(17165627042258546)
            ->sync();

        $this->assertInstanceOf(PNFetchMessagesResult::class, $response);
        $this->assertEquals(1, count($response->getChannels()[self::CHANNEL_NAME]));
        $this->assertEquals(
            'hello ' . self::CHANNEL_NAME . ' channel. Message: 5',
            $response->getChannels()[self::CHANNEL_NAME][0]->getMessage()
        );
    }

    public function testFetchEncrypted()
    {
        $subKey = $this->pubnub_enc->getConfiguration()->getSubscribeKey();
        $fetchMessages = new FetchMessagesExposed($this->pubnub_enc);

        $fetchMessages
            ->stubFor("/v3/history/sub-key/{$subKey}/channel/TheMessageHistoryChannelHD-ENCRYPTED")
            ->withQuery([
                "uuid" => $this->pubnub_enc->getConfiguration()->getUserId(),
                "pnsdk" => $this->encodedSdkName,
                "include_meta" => "false",
                "include_uuid" => "false",
                "include_message_type" => "true",
                "include_custom_message_type" => "false",
            ])

            ->setResponseBody('{"status": 200, "error": false, "error_message": "", "channels": {
                "TheMessageHistoryChannelHD-ENCRYPTED":[
                    {"message":"CRD1ctIrZLGyFa4qqQcQVfvSOWeSNkPdxCs9CEsA/eE3Et3mfZaTDV3ANv1l/pc/",
                        "timetoken":"17165627054255980"
                    }
                ]}}');

        $response = $fetchMessages->channels(self::ENCRYPTED_CHANNEL_NAME)
            ->sync();

        $this->assertInstanceOf(PNFetchMessagesResult::class, $response);
        $this->assertEquals(1, count($response->getChannels()[self::ENCRYPTED_CHANNEL_NAME]));

        $this->assertEquals(
            'Hey. This one is a secret ;-)',
            $response->getChannels()[self::ENCRYPTED_CHANNEL_NAME][0]->getMessage()
        );
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class FetchMessagesExposed extends FetchMessages
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
