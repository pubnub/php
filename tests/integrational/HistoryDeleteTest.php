<?php

namespace Tests\Integrational;

use PubNub\Exceptions\PubNubServerException;
use PubNub\PubNub;
use PubNub\Endpoints\HistoryDelete;
use PubNub\Exceptions\PubNubValidationException;
use Tests\Helpers\Stub;
use Tests\Helpers\StubTransport;

class TestPubNubHistoryDelete extends \PubNubTestCase
{
    public function testMissingChannelException()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $historyDelete = new HistoryDeleteExposed($this->pubnub);

        $historyDelete->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => Stub::ANY
            ])
            ->setResponseBody(json_encode([]));

        $historyDelete->sync();
    }

    public function testChannelIsEmptyException()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $historyDelete = new HistoryDeleteExposed($this->pubnub);

        $historyDelete->channel("")->sync();
    }

    // This test will require a key with specific permissions assigned
    // public function testNotPermitted()
    // {
    //     $channel = "history-delete-php-ch";
    //     $this->expectException(PubNubServerException::class);

    //     $this->pubnub_pam->getConfiguration()->setSecretKey(null);
    //     $this->pubnub_pam->deleteMessages()->channel($channel)->start(123)->end(456)->sync();
    // }

    public function testSuperCallTest()
    {
        $this->expectNotToPerformAssertions();
        $this->pubnub_pam->deleteMessages()
            ->channel(static::SPECIAL_CHARACTERS)
            ->sync();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class HistoryDeleteExposed extends HistoryDelete
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
