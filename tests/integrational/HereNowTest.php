<?php

namespace Tests\Integrational;

use PubNub\PubNub;
use PubNub\Endpoints\Presence\HereNow;
use PubNub\Exceptions\PubNubValidationException;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class HereNowTest extends \PubNubTestCase
{
    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testMultipleChannelState()
    {
        $hereNow = new HereNowExposed($this->pubnub_demo);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/ch1,ch2")
            ->withQuery([
                'state' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"total_occupancy\":3,\"total_channels\""
                . ":2,\"channels\":{\"ch1\":{\"occupancy\":1,\"uuids\":[{\"uuid\":\"user1\"}]},\"ch2\":{\"occupancy\":2"
                . ",\"uuids\":[{\"uuid\":\"user1\"},{\"uuid\":\"user3\"}]}}},\"service\":\"Presence\"}");

        $response = $hereNow->channels(["ch1", "ch2"])->includeState(true)->sync();

        $this->assertEquals(2, $response->getTotalChannels());
        $this->assertEquals(3, $response->getTotalOccupancy());

        $this->assertEquals($response->getChannels()[0]->getChannelName(), "ch1");
        $this->assertEquals($response->getChannels()[0]->getOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()[0]->getOccupants()), 1);
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getUuid(), "user1");
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getState(), null);

        $this->assertEquals($response->getChannels()[1]->getChannelName(), "ch2");
        $this->assertEquals($response->getChannels()[1]->getOccupancy(), 2);
        $this->assertEquals(count($response->getChannels()[1]->getOccupants()), 2);
        $this->assertEquals($response->getChannels()[1]->getOccupants()[0]->getUuid(), "user1");
        $this->assertEquals($response->getChannels()[1]->getOccupants()[0]->getState(), null);
        $this->assertEquals($response->getChannels()[1]->getOccupants()[1]->getUuid(), "user3");
        $this->assertEquals($response->getChannels()[1]->getOccupants()[1]->getState(), null);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testMultipleChannel()
    {
        $hereNow = new HereNowExposed($this->pubnub_demo);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/ch1,ch2")
            ->withQuery([
                'state' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"total_occupancy\":3,\"total_channels\""
                . ":2,\"channels\":{\"ch1\":{\"occupancy\":1,\"uuids\":[{\"uuid\":\"user1\"}]},\"ch2\":{\"occupancy\":2"
                . ",\"uuids\":[{\"uuid\":\"user1\"},{\"uuid\":\"user3\"}]}}},\"service\":\"Presence\"}");

        $response = $hereNow->channels(["ch1", "ch2"])->includeState(true)->sync();

        $this->assertEquals(2, $response->getTotalChannels());
        $this->assertEquals(3, $response->getTotalOccupancy());

        $this->assertEquals($response->getChannels()[0]->getChannelName(), "ch1");
        $this->assertEquals($response->getChannels()[0]->getOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()[0]->getOccupants()), 1);
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getUuid(), "user1");
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getState(), null);

        $this->assertEquals($response->getChannels()[1]->getChannelName(), "ch2");
        $this->assertEquals($response->getChannels()[1]->getOccupancy(), 2);
        $this->assertEquals(count($response->getChannels()[1]->getOccupants()), 2);
        $this->assertEquals($response->getChannels()[1]->getOccupants()[0]->getUuid(), "user1");
        $this->assertEquals($response->getChannels()[1]->getOccupants()[0]->getState(), null);
        $this->assertEquals($response->getChannels()[1]->getOccupants()[1]->getUuid(), "user3");
        $this->assertEquals($response->getChannels()[1]->getOccupants()[1]->getState(), null);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testMultipleChannelWithoutState()
    {
        $hereNow = new HereNowExposed($this->pubnub_demo);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/game1,game2")
            ->withQuery([
                'pnsdk' => $this->encodedSdkName,
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": {\"game1\": {\"uuids"
                . "\": [\"a3ffd012-a3b9-478c-8705-64089f24d71e\"], \"occupancy\": 1}}, \"total_channels\": 1, \"total_o"
                . "ccupancy\": 1}, \"service\": \"Presence\"}");

        $response = $hereNow->channels(["game1", "game2"])->includeState(false)->sync();

        $this->assertEquals($response->getTotalChannels(), 1);
        $this->assertEquals($response->getTotalOccupancy(), 1);

        $this->assertEquals($response->getChannels()[0]->getChannelName(), "game1");
        $this->assertEquals($response->getChannels()[0]->getOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()[0]->getOccupants()), 1);
        $this->assertEquals(
            $response->getChannels()[0]->getOccupants()[0]->getUuid(),
            "a3ffd012-a3b9-478c-8705-64089f24d71e"
        );
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getState(), null);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testMultipleChannelWithoutStateUUIDs()
    {
        $hereNow = new HereNowExposed($this->pubnub_demo);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/game1,game2")
            ->withQuery([
                'disable-uuids' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": {\"game1\": {\"occupa"
                . "ncy\": 1}}, \"total_channels\": 1, \"total_occupancy\": 1}, \"service\": \"Presence\"}");

        $response = $hereNow->channels(["game1", "game2"])->includeUuids(false)->includeState(false)->sync();

        $this->assertEquals($response->getTotalChannels(), 1);
        $this->assertEquals($response->getTotalOccupancy(), 1);

        $this->assertEquals($response->getChannels()[0]->getChannelName(), "game1");
        $this->assertEquals($response->getChannels()[0]->getOccupancy(), 1);
        $this->assertEquals(is_array($response->getChannels()[0]->getOccupants()), false);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testSingularChannelWithoutStateUUIDs()
    {
        $hereNow = new HereNowExposed($this->pubnub_demo);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/game1")
            ->withQuery([
                'disable-uuids' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"service\": \"Presence\", \"occupancy\": 3}");

        $response = $hereNow->channels("game1")->includeUuids(false)->includeState(false)->sync();

        $this->assertEquals($response->getTotalChannels(), 1);
        $this->assertEquals($response->getTotalOccupancy(), 3);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testSingularChannelWithoutState()
    {
        $hereNow = new HereNowExposed($this->pubnub_demo);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/game1")
            ->withQuery([
                'pnsdk' => $this->encodedSdkName,
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"service\": \"Presence\", \"uuids\": [\"a3ffd012"
                . "-a3b9-478c-8705-64089f24d71e\"], \"occupancy\": 1}");

        $response = $hereNow->channels("game1")->includeState(false)->sync();

        $this->assertEquals($response->getTotalChannels(), 1);
        $this->assertEquals($response->getTotalOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()), 1);
        $this->assertEquals($response->getChannels()[0]->getOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()[0]->getOccupants()), 1);
        $this->assertEquals(
            $response->getChannels()[0]->getOccupants()[0]->getUuid(),
            "a3ffd012-a3b9-478c-8705-64089f24d71e"
        );
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getState(), null);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testSingularChannel()
    {
        $hereNow = new HereNowExposed($this->pubnub_demo);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/game1")
            ->withQuery([
                'state' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"service\":\"Presence\",\"uuids\":[{\"uuid\":\"a3ffd"
                . "012-a3b9-478c-8705-64089f24d71e\",\"state\":{\"age\":10}}],\"occupancy\":1}");

        $response = $hereNow->channels("game1")->includeState(true)->sync();

        $this->assertEquals($response->getTotalChannels(), 1);
        $this->assertEquals($response->getTotalOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()), 1);
        $this->assertEquals($response->getChannels()[0]->getOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()[0]->getOccupants()), 1);
        $this->assertEquals(
            $response->getChannels()[0]->getOccupants()[0]->getUuid(),
            "a3ffd012-a3b9-478c-8705-64089f24d71e"
        );
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getState(), ["age" => 10]);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testSingularChannelAndGroup()
    {
        $hereNow = new HereNowExposed($this->pubnub_demo);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/game1")
            ->withQuery([
                'channel-group' => 'grp1',
                'state' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"channels\":{}, \"total_channels\":0, "
                . "\"total_occupancy\":0},\"service\":\"Presence\"}");

        $response = $hereNow->channelGroups("grp1")->channels("game1")->includeState(true)->sync();

        $this->assertEquals($response->getTotalOccupancy(), 0);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testIsAuthRequiredSuccess()
    {
        $this->expectNotToPerformAssertions();
        $hereNow = new HereNowExposed($this->pubnub_demo);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/ch1,ch2")
            ->withQuery([
                'state' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
                'auth' => 'myKey'
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"total_occupancy\":3,\"total_channels"
                . "\":2,\"channels\":{\"ch1\":{\"occupancy\":1,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}}]}"
                . ",\"ch2\":{\"occupancy\":2,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}},{\"uuid\":\"user3"
                . "\",\"state\":{\"age\":30}}]}}},\"service\":\"Presence\"}");

        $this->pubnub_demo->getConfiguration()->setAuthKey("myKey");

        $hereNow->channels(["ch1", "ch2"])->includeState(true)->sync();
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testNullSubKey()
    {
        $this->expectException(\TypeError::class);
        $config = $this->config->clone();
        $config->setSubscribeKey(null);

        $pubnub = new \PubNub\PubNub($config);
        $hereNow = new HereNowExposed($pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/ch1,ch2")
            ->withQuery([
                'channel-group' => 'grp1',
                'state' => '1',
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"total_occupancy\":3,\"total_channels"
                . "\":2,\"channels\":{\"ch1\":{\"occupancy\":1,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}}]}"
                . ",\"ch2\":{\"occupancy\":2,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}},{\"uuid\":\"user3"
                . "\",\"state\":{\"age\":30}}]}}},\"service\":\"Presence\"}");

        $hereNow->channels(["ch1", "ch2"])->includeState(true)->sync();
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testEmptySubKey()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");
        $config = $this->config->clone();
        $config->setSubscribeKey("");

        $pubnub = new \PubNub\PubNub($config);
        $hereNow = new HereNowExposed($pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/demo/channel/ch1,ch2")
            ->withQuery([
                'state' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => $this->pubnub_demo->getConfiguration()->getUuid(),
                'auth' => 'myKey'
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"total_occupancy\":3,\"total_channels"
                . "\":2,\"channels\":{\"ch1\":{\"occupancy\":1,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}}]}"
                . ",\"ch2\":{\"occupancy\":2,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}},{\"uuid\":\"user3\""
                . ",\"state\":{\"age\":30}}]}}},\"service\":\"Presence\"}");

        $hereNow->channels(["ch1", "ch2"])->includeState(true)->sync();
    }

    public function testSuperCallTest()
    {
        $this->expectNotToPerformAssertions();
        // Not valid
        // ,~/
        $characters = "-._:?#[]@!$&'()*+;=`|";

        $this->pubnub_pam->hereNow()
            ->channels($characters)
            ->sync();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class HereNowExposed extends HereNow
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
