<?php

namespace Tests\Integrational;

use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Models\Consumer\History\PNHistoryResult;
use PubNub\PubNub;
use PubNub\Endpoints\History;
use PubNub\Exceptions\PubNubValidationException;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class HistoryTest extends \PubNubTestCase
{
    /**
     * @group history
     * @group history-integrational
     */
    public function testSuccess()
    {
        $history = new HistoryExposed($this->pubnub_demo);

        $testArray = [];
        $historyItems = [];
        $historyEnvelope1 = [];
        $historyItem1 = [];

        $historyItem1["a"] = 11;
        $historyItem1["b"] = 22;

        $historyEnvelope1["timetoken"] = 1111;
        $historyEnvelope1["message"] = $historyItem1;

        $historyEnvelope2 = [];
        $historyItem2 = [];

        $historyItem2["a"] = 33;
        $historyItem2["b"] = 44;

        $historyEnvelope2["timetoken"] = 2222;
        $historyEnvelope2["message"] = $historyItem2;

        $historyItems[] = $historyEnvelope1;
        $historyItems[] = $historyEnvelope2;

        $testArray[] = $historyItems;
        $testArray[] = 1234;
        $testArray[] = 4321;

        $history->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "count" => "100",
                "include_token" => "true",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid()
            ])
            ->setResponseBody(json_encode($testArray));

        $response = $history->channel("niceChannel")->includeTimetoken(true)->sync();

        $this->assertEquals($response->getStartTimetoken(), 1234);
        $this->assertEquals($response->getEndTimetoken(), 4321);
        $this->assertEquals(count($response->getMessages()), 2);
        $this->assertEquals($response->getMessages()[0]->getTimetoken(), 1111);
        $this->assertEquals($response->getMessages()[0]->getEntry()["a"], 11);
        $this->assertEquals($response->getMessages()[0]->getEntry()["b"], 22);
        $this->assertEquals($response->getMessages()[1]->getTimetoken(), 2222);
        $this->assertEquals($response->getMessages()[1]->getEntry()["a"], 33);
        $this->assertEquals($response->getMessages()[1]->getEntry()["b"], 44);
    }

    /**
     * @group history
     * @group history-integrational
     */
    public function testAuthSuccess()
    {
        $this->expectNotToPerformAssertions();
        $this->pubnub_demo->getConfiguration()->setAuthKey("blah");
        $history = new HistoryExposed($this->pubnub_demo);

        $testArray = [];
        $historyItems = [];
        $historyEnvelope1 = [];
        $historyItem1 = [];

        $historyItem1["a"] = 11;
        $historyItem1["b"] = 22;

        $historyEnvelope1["timetoken"] = 1111;
        $historyEnvelope1["message"] = $historyItem1;

        $historyEnvelope2 = [];
        $historyItem2 = [];

        $historyItem2["a"] = 33;
        $historyItem2["b"] = 44;

        $historyEnvelope2["timetoken"] = 2222;
        $historyEnvelope2["message"] = $historyItem2;

        $historyItems[] = $historyEnvelope1;
        $historyItems[] = $historyEnvelope2;

        $testArray[] = $historyItems;
        $testArray[] = 1234;
        $testArray[] = 4321;

        $history->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "count" => "100",
                "include_token" => "true",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid(),
                "auth" => "blah"
            ])
            ->setResponseBody(json_encode($testArray));

        $this->pubnub->getConfiguration()->setAuthKey("auth");

        $history->channel("niceChannel")->includeTimetoken(true)->sync();
    }

    /**
     * @group history
     * @group history-integrational
     */
    public function testEncryptedSuccess()
    {
        $config = $this->config->clone();
        $config->setUseRandomIV(false);
        $config->setCipherKey("cipherKey");
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("demo");
        $pubnub = new PubNub($config);
        $history = new HistoryExposed($pubnub);

        $history->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "count" => "100",
                "include_token" => "true",
                "pnsdk" => $pubnub->getSdkFullName(),
                "uuid" => $pubnub->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("[[{\"message\":\"zFJeF9BVABL80GUiQEBjLg==\","
                . "\"timetoken\":\"14649369736959785\"},"
                . "{\"message\":\"HIq4MTi9nk/KEYlHOKpMCaH78ZXppGynDHrgY9nAd3s=\","
                . "\"timetoken\":\"14649369766426772\"}],"
                . " 14649369736959785,14649369766426772]");

        $response = $history->channel("niceChannel")->includeTimetoken(true)->sync();

        $this->assertTrue($response->getStartTimetoken() === 14649369736959785);
        $this->assertTrue($response->getEndTimetoken() === 14649369766426772);

        $this->assertEquals(count($response->getMessages()), 2);

        $this->assertEquals($response->getMessages()[0]->getTimetoken(), "14649369736959785");
        $this->assertEquals($response->getMessages()[0]->getEntry()->text, "hey");

        $this->assertEquals($response->getMessages()[1]->getTimetoken(), "14649369766426772");
        $this->assertEquals($response->getMessages()[1]->getEntry()->text2, "hey2");
    }

    public function testEncryptedWithPNOtherSuccess()
    {
        $config = $this->config->clone();
        $config->setUseRandomIV(false);
        $config->setCipherKey("hello");
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("demo");
        $pubnub = new PubNub($config);
        $history = new HistoryExposed($pubnub);

        $history->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "count" => "100",
                "include_token" => "false",
                "pnsdk" => $pubnub->getSdkFullName(),
                "uuid" => $pubnub->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("[[{\"pn_other\":\"6QoqmS9CnB3W9+I4mhmL7w==\"}],14606134331557852,14606134485013970]");

        $response = $history->channel("niceChannel")->includeTimetoken(false)->sync();

        $this->assertTrue($response->getStartTimetoken() === 14606134331557852);
        $this->assertTrue($response->getEndTimetoken() === 14606134485013970);

        $this->assertEquals(count($response->getMessages()), 1);
    }

    public function testSuccessWithoutTimeToken()
    {
        $history = new HistoryExposed($this->pubnub_demo);

        $testArray = [];
        $historyItems = [];
        $historyItem1 = [];
        $historyItem2 = [];

        $historyItem1["a"] = 11;
        $historyItem1["b"] = 22;

        $historyItem2["a"] = 33;
        $historyItem2["b"] = 44;

        $historyItems[] = $historyItem1;
        $historyItems[] = $historyItem2;

        $testArray[] = $historyItems;
        $testArray[] = 1234;
        $testArray[] = 4321;

        $history->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "count" => "100",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid()
            ])
            ->setResponseBody(json_encode($testArray));

        $response = $history->channel("niceChannel")->sync();

        $this->assertTrue($response->getStartTimetoken() === 1234);
        $this->assertTrue($response->getEndTimetoken() === 4321);

        $this->assertEquals(count($response->getMessages()), 2);

        $this->assertNull($response->getMessages()[0]->getTimetoken());
        $this->assertEquals($response->getMessages()[0]->getEntry()["a"], 11);
        $this->assertEquals($response->getMessages()[0]->getEntry()["b"], 22);

        $this->assertNull($response->getMessages()[1]->getTimetoken());
        $this->assertEquals($response->getMessages()[1]->getEntry()["a"], 33);
        $this->assertEquals($response->getMessages()[1]->getEntry()["b"], 44);
    }

    public function testMissingChannel()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $history = new HistoryExposed($this->pubnub_demo);

        $history->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody(json_encode([]));

        $history->includeTimetoken(true)->sync();
    }

    public function testChannelIsEmpty()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $history = new HistoryExposed($this->pubnub_demo);

        $history->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid()
            ])
            ->setResponseBody(json_encode([]));

        $history->channel("")->includeTimetoken(true)->sync();
    }

    public function testCountReverseStartEndSuccess()
    {
        $history = new HistoryExposed($this->pubnub_demo);

        $testArray = [];
        $historyItems = [];
        $historyEnvelope1 = [];
        $historyItem1 = [];

        $historyItem1["a"] = 11;
        $historyItem1["b"] = 22;

        $historyEnvelope1["timetoken"] = 1111;
        $historyEnvelope1["message"] = $historyItem1;

        $historyEnvelope2 = [];
        $historyItem2 = [];

        $historyItem2["a"] = 33;
        $historyItem2["b"] = 44;

        $historyEnvelope2["timetoken"] = 2222;
        $historyEnvelope2["message"] = $historyItem2;

        $historyItems[] = $historyEnvelope1;
        $historyItems[] = $historyEnvelope2;

        $testArray[] = $historyItems;
        $testArray[] = 1234;
        $testArray[] = 4321;

        $history->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "start" => "1",
                "end" => "2",
                "count" => "5",
                "reverse" => "true",
                "include_token" => "true",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid()
            ])
            ->setResponseBody(json_encode($testArray));

        $response = $history->channel("niceChannel")
            ->count(5)
            ->reverse(true)
            ->start(1)
            ->end(2)
            ->includeTimetoken(true)
            ->sync();

        $this->assertTrue($response->getStartTimetoken() === 1234);
        $this->assertTrue($response->getEndTimetoken() === 4321);

        $this->assertEquals(count($response->getMessages()), 2);

        $this->assertTrue($response->getMessages()[0]->getTimetoken() === 1111);
        $this->assertEquals($response->getMessages()[0]->getEntry()["a"], 11);
        $this->assertEquals($response->getMessages()[0]->getEntry()["b"], 22);

        $this->assertTrue($response->getMessages()[1]->getTimetoken() === 2222);
        $this->assertEquals($response->getMessages()[1]->getEntry()["a"], 33);
        $this->assertEquals($response->getMessages()[1]->getEntry()["b"], 44);
    }

    public function testProcessMessageError()
    {
        $this->markTestSkipped('must be revisited.');
        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage("Decryption error: message is not a string");

        $history = new HistoryExposed($this->pubnub_demo);

        $this->pubnub_demo->getConfiguration()->setCipherKey("Test");

        $testArray = [];
        $historyItems = [];
        $historyEnvelope1 = [];
        $historyItem1 = [];

        $historyItem1["a"] = 11;
        $historyItem1["b"] = 22;

        $historyEnvelope1["timetoken"] = 1111;
        $historyEnvelope1["message"] = $historyItem1;

        $historyEnvelope2 = [];
        $historyItem2 = [];

        $historyItem2["a"] = 33;
        $historyItem2["b"] = 44;

        $historyEnvelope2["timetoken"] = 2222;
        $historyEnvelope2["message"] = $historyItem2;

        $historyItems[] = $historyEnvelope1;
        $historyItems[] = $historyEnvelope2;

        $testArray[] = $historyItems;
        $testArray[] = 1234;
        $testArray[] = 4321;

        $history->stubFor("/v2/history/sub-key/demo/channel/niceChannel")
            ->withQuery([
                "start" => "1",
                "end" => "2",
                "count" => "5",
                "reverse" => "true",
                "include_token" => "true",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid()
            ])
            ->setResponseBody(json_encode($testArray));

        $history->channel("niceChannel")
            ->count(5)
            ->reverse(true)
            ->start(1)
            ->end(2)
            ->includeTimetoken(true)
            ->sync();
    }

    // This test will require a key with specific permissions assigned
    // public function testNotPermitted()
    // {
    //     $ch = "history-php-ch";
    //     $this->expectException(PubNubServerException::class);

    //     $this->pubnub_pam->getConfiguration()->setSecretKey(null);
    //     $this->pubnub_pam->history()->channel($ch)->count(static::COUNT)->sync();
    // }

    // TODO: fix test
    public function xtestSuperCallWithChannelOnly()
    {
        $ch = "history-php-ch-.*|@#";

        $config = $this->config_pam->clone();
        $config->setUuid("history-php-uuid-.*|@#");

        $pubnub_pam = new PubNub($config);

        $result = $pubnub_pam->history()->channel($ch)->sync();

        $this->assertInstanceOf(PNHistoryResult::class, $result);
    }

    public function testSuperCallWithAllParams()
    {
        $ch = "history-php-ch";
        $config = $this->config_pam->clone();
        $config->setUuid("history-php-uuid");

        $pubnub_pam = new PubNub($config);

        $result = $pubnub_pam->history()
            ->channel($ch)
            ->count(2)
            ->includeTimetoken(true)
            ->reverse(true)
            ->start(1)
            ->end(2)
            ->sync();

        $this->assertInstanceOf(PNHistoryResult::class, $result);
    }

    public function testSuperCallTest()
    {
        $this->expectNotToPerformAssertions();
        $this->pubnub_pam->history()
            ->channel(static::SPECIAL_CHARACTERS)
            ->sync();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class HistoryExposed extends History
{
    protected PsrStubClient $client;

    public function __construct(PubNub $pubnubInstance)
    {
        parent::__construct($pubnubInstance);
        $this->client = new PsrStubClient();
        $pubnubInstance->setClient($this->client);
    }

    public function stubFor(string $url): PsrStub
    {
        $stub = new PsrStub($url);
        $this->client->addStub($stub);
        return $stub;
    }
}
