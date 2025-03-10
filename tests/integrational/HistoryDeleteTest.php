<?php

namespace Tests\Integrational;

use PubNub\PubNub;
use PubNub\Endpoints\HistoryDelete;
use PubNub\Exceptions\PubNubValidationException;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class HistoryDeleteTest extends \PubNubTestCase
{
    public function testMissingChannelException()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $historyDelete = new HistoryDeleteExposed($this->pubnub_demo);

        $historyDelete->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody(json_encode([]));

        $historyDelete->sync();
    }

    public function testChannelIsEmptyException()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");
        $this->pubnub->deleteMessages()->channel("")->sync();
    }

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
