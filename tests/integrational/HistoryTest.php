<?php

namespace Tests\Integrational;

use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\History\PNHistoryResult;
use PubNub\PubNub;
use PubNub\Endpoints\History;
use PubNub\Exceptions\PubNubValidationException;
use Tests\Helpers\Stub;
use Tests\Helpers\StubTransport;


class TestPubNubHistory extends \PubNubTestCase
{
    const COUNT = 5;
    const TOTAL = 7;

    /**
     * @group history
     * @group history-integrational
     */
    public function testSuccess()
    {
        $history = new HistoryExposed($this->pubnub);

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

        $history->stubFor("/v2/history/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/niceChannel")
            ->withQuery([
                "count" => "100",
                "include_token" => "true",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => Stub::ANY
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
        $this->pubnub->getConfiguration()->setAuthKey("blah");
        $history = new HistoryExposed($this->pubnub);

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

        $history->stubFor("/v2/history/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/niceChannel")
            ->withQuery([
                "count" => "100",
                "include_token" => "true",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => Stub::ANY,
                "auth" => "auth"
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
        $history = new HistoryExposed($this->pubnub);

        $this->pubnub->getConfiguration()->setCipherKey("testCipher");

        $history->stubFor("/v2/history/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/niceChannel")
            ->withQuery([
                "count" => "100",
                "include_token" => "false",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => Stub::ANY
            ])
            ->setResponseBody("[[\"EGwV+Ti43wh2TprPIq7o0KMuW5j6B3yWy352ucWIOmU=\\n\",\"EGwV+Ti43wh2TprPIq7o0KMuW5j6B3yWy352ucWIOmU=\\n\",\"EGwV+Ti43wh2TprPIq7o0KMuW5j6B3yWy352ucWIOmU=\\n\"],14606134331557853,14606134485013970]");

        $response = $history->channel("niceChannel")->includeTimetoken(false)->sync();

        $this->assertTrue($response->getStartTimetoken() === 14606134331557853);
        $this->assertTrue($response->getEndTimetoken() === 14606134485013970);

        $this->assertEquals(count($response->getMessages()), 3);

        $this->assertEquals(count($response->getMessages()[0]->getTimetoken()), null);
        $this->assertEquals($response->getMessages()[0]->getEntry()[0], "m1");
        $this->assertEquals($response->getMessages()[0]->getEntry()[1], "m2");
        $this->assertEquals($response->getMessages()[0]->getEntry()[2], "m3");

        $this->assertEquals($response->getMessages()[1]->getEntry()[0], "m1");
        $this->assertEquals($response->getMessages()[1]->getEntry()[1], "m2");
        $this->assertEquals($response->getMessages()[1]->getEntry()[2], "m3");

        $this->assertEquals($response->getMessages()[2]->getEntry()[0], "m1");
        $this->assertEquals($response->getMessages()[2]->getEntry()[1], "m2");
        $this->assertEquals($response->getMessages()[2]->getEntry()[2], "m3");
    }

    public function testEncryptedWithPNOtherSuccess()
    {
        $history = new HistoryExposed($this->pubnub);

        $this->pubnub->getConfiguration()->setCipherKey("hello");

        $history->stubFor("/v2/history/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/niceChannel")
            ->withQuery([
                "count" => "100",
                "include_token" => "false",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => Stub::ANY
            ])
            ->setResponseBody("[[{\"pn_other\":\"6QoqmS9CnB3W9+I4mhmL7w==\"}],14606134331557852,14606134485013970]");

        $response = $history->channel("niceChannel")->includeTimetoken(false)->sync();

        $this->assertTrue($response->getStartTimetoken() === 14606134331557852);
        $this->assertTrue($response->getEndTimetoken() === 14606134485013970);

        $this->assertEquals(count($response->getMessages()), 1);
    }

    public function testSuccessWithoutTimeToken()
    {
        $history = new HistoryExposed($this->pubnub);

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

        $history->stubFor("/v2/history/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/niceChannel")
            ->withQuery([
                "count" => "100",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => Stub::ANY
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

        $history = new HistoryExposed($this->pubnub);

        $history->stubFor("/v2/history/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/niceChannel")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => Stub::ANY
            ])
            ->setResponseBody(json_encode([]));

        $history->includeTimetoken(true)->sync();
    }

    public function testChannelIsEmpty()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $history = new HistoryExposed($this->pubnub);

        $history->stubFor("/v2/history/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/niceChannel")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => Stub::ANY
            ])
            ->setResponseBody(json_encode([]));

        $history->channel("")->includeTimetoken(true)->sync();
    }

    public function testCountReverseStartEndSuccess()
    {
        $history = new HistoryExposed($this->pubnub);

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

        $history->stubFor("/v2/history/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/niceChannel")
            ->withQuery([
                "start" => "1",
                "end" => "2",
                "count" => "5",
                "reverse" => "true",
                "include_token" => "true",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => Stub::ANY
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
        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage("Decryption error: message is not a string");

        $history = new HistoryExposed($this->pubnub);

        $this->pubnub->getConfiguration()->setCipherKey("Test");

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

        $history->stubFor("/v2/history/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/niceChannel")
            ->withQuery([
                "start" => "1",
                "end" => "2",
                "count" => "5",
                "reverse" => "true",
                "include_token" => "true",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => Stub::ANY
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

    public function testNotPermitted()
    {
        $ch = "history-php-ch";
        $this->expectException(PubNubServerException::class);

        $this->pubnub_pam->getConfiguration()->setSecretKey(null);
        $this->pubnub_pam->history()->channel($ch)->count(static::COUNT)->sync();
    }

    // TODO: fix test
    public function xtestSuperCallWithChannelOnly()
    {
        $ch = "history-php-ch-.*|@#";

        $this->pubnub_pam->getConfiguration()->setUuid("history-php-uuid-.*|@#");

        $result = $this->pubnub_pam->history()->channel($ch)->sync();

        $this->assertInstanceOf(PNHistoryResult::class, $result);
    }

    public function testSuperCallWithAllParams()
    {
        $ch = "history-php-ch";

        $this->pubnub_pam->getConfiguration()->setUuid("history-php-uuid");

        $result = $this->pubnub_pam->history()
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
        $res = $this->pubnub_pam->history()
            ->channel(static::SPECIAL_CHARACTERS)
            ->sync();
    }
}

class HistoryExposed extends History
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
