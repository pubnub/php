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

        $historyDelete->stubFor("/v2/history/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/niceChannel")
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

    public function testNotPermitted()
    {
        $ch = "history-delete-php-ch";
        $this->expectException(PubNubServerException::class);

        $this->pubnub_pam->getConfiguration()->setSecretKey(null);
        $this->pubnub_pam->deleteMessages()->channel($ch)->start(123)->end(456)->sync();
    }

    public function testSuperCallTest()
    {
        $this->pubnub_pam->deleteMessages()
            ->channel(static::SPECIAL_CHARACTERS)
            ->sync();
    }
}

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
