<?php

namespace Tests\Functional;

use PubNubTestCase;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\PNMessageType;

class SignalTest extends PubNubTestCase
{
    public function testSignalSuccess()
    {
        $response = $this->pubnub->Signal()
            ->channel("ch")
            ->message("test")
            ->sync();

        $this->assertNotEmpty($response);
        $data = $response->getTimetoken();
        $this->assertNotEmpty($data);
    }

    public function testSignalMissingChannel()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel Missing");

        $response = $this->pubnub->Signal()
            ->message("test")
            ->sync();
    }

    public function testSignalMissingMessage()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Message Missing");

        $response = $this->pubnub->Signal()
            ->channel("ch")
            ->sync();
    }

    public function testSignalWithSpace()
    {
        $response = $this->pubnub->Signal()
            ->channel("ch")
            ->message("test")
            ->spaceId('HelloSpace')
            ->sync();

        $this->assertNotEmpty($response);
        $data = $response->getTimetoken();
        $this->assertNotEmpty($data);
    }

    public function testSignalWithMessageType()
    {
        $response = $this->pubnub->Signal()
            ->channel("ch")
            ->message("test")
            ->messageType(new PNMessageType('HelloMessageType'))
            ->sync();

        $this->assertNotEmpty($response);
        $data = $response->getTimetoken();
        $this->assertNotEmpty($data);
    }
}
