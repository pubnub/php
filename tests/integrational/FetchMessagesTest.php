<?php

namespace Tests\Integrational;

use PubNub\Models\Consumer\MessagePersistence\PNFetchMessagesResult;
use PubNubTestCase;

class FetchMessagesTest extends PubNubTestCase
{
    protected const CHANNEL_NAME = 'TheMessageHistoryChannelHD';
    protected const ENCRYPTED_CHANNEL_NAME = 'TheMessageHistoryChannelHD-ENCRYPTED';


    protected const MESSAGE_COUNT = 10;

    protected $startTimetoken = null;
    protected $middleTimetoken = null;
    protected $endTimetoken = null;

    public function setup(): void
    {
        parent::setUp();

        $firstMessage = $this->pubnub->publish()
            ->channel(self::CHANNEL_NAME)
            ->message('hello ' . self::CHANNEL_NAME . ' channel. First message')
            ->meta(['FIRST_MESSAGE' => true])
            ->sync();

        $this->startTimetoken = $firstMessage->getTimetoken();
        $middleMessage = round(self::MESSAGE_COUNT / 2);

        for ($i = 2; $i <= self::MESSAGE_COUNT; $i++) {
            $messageResult = $this->pubnub->publish()
                ->channel(self::CHANNEL_NAME)
                ->message('hello ' . self::CHANNEL_NAME . ' channel. Message: ' . $i)
                ->sync();

            if ($i == $middleMessage) {
                $this->middleTimetoken = $messageResult->getTimetoken();
            }

            $this->endTimetoken = $messageResult->getTimetoken();
        };

        $this->pubnub_enc->publish()
            ->channel(self::ENCRYPTED_CHANNEL_NAME)
            ->message('Hey. This one is a secret ;-)')
            ->sync();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->pubnub->deleteMessages()
            ->channel(self::CHANNEL_NAME)
            ->sync();
        $this->pubnub->deleteMessages()
            ->channel(self::ENCRYPTED_CHANNEL_NAME)
            ->sync();
    }

    public function testFetchMessages()
    {
        $this->caseFetchWithDefaults();
        $this->caseFetchWithCount();
        $this->caseFetchWithStartEnd();
        $this->caseFetchEncrypted();
    }

    protected function caseFetchWithDefaults()
    {
        $messages = $this->pubnub->fetchMessages()
            ->channels(self::CHANNEL_NAME)
            ->count(100)
            ->sync();

        $this->assertInstanceOf(PNFetchMessagesResult::class, $messages);

        $this->assertEquals(
            self::MESSAGE_COUNT,
            count($messages->getChannels()[self::CHANNEL_NAME])
        );
    }

    protected function caseFetchWithCount()
    {
        $messages = $this->pubnub->fetchMessages()
            ->channels(self::CHANNEL_NAME)
            ->count(5)
            ->sync();

        $this->assertInstanceOf(PNFetchMessagesResult::class, $messages);

        $this->assertEquals(5, count($messages->getChannels()[self::CHANNEL_NAME]));
    }

    protected function caseFetchWithStartEnd()
    {
        $messages = $this->pubnub->fetchMessages()
            ->channels(self::CHANNEL_NAME)
            ->start($this->middleTimetoken - 100)
            ->end($this->middleTimetoken + 100)
            ->sync();

        $this->assertInstanceOf(PNFetchMessagesResult::class, $messages);
        $middleMessage = round(self::MESSAGE_COUNT / 2);
        $this->assertEquals(1, count($messages->getChannels()[self::CHANNEL_NAME]));
        $this->assertEquals(
            'hello ' . self::CHANNEL_NAME . ' channel. Message: ' . $middleMessage,
            $messages->getChannels()[self::CHANNEL_NAME][0]->getMessage()
        );
    }

    protected function caseFetchEncrypted()
    {

        $messages = $this->pubnub_enc->fetchMessages()
            ->channels(self::ENCRYPTED_CHANNEL_NAME)
            ->sync();

        $this->assertInstanceOf(PNFetchMessagesResult::class, $messages);
        $this->assertEquals(1, count($messages->getChannels()[self::ENCRYPTED_CHANNEL_NAME]));

        $this->assertEquals(
            'Hey. This one is a secret ;-)',
            $messages->getChannels()[self::ENCRYPTED_CHANNEL_NAME][0]->getMessage()
        );
    }
}
