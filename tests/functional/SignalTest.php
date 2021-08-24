<?php

namespace Tests\Functional;

use PubNubTestCase;
use PubNub\Exceptions\PubNubValidationException;


class SignalEndpointTest extends PubNubTestCase
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
}
