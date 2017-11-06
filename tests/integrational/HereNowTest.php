<?php

namespace Tests\Integrational;

use PubNub\Endpoints\Presence\HereNow;
use PubNub\Exceptions\PubNubValidationException;
use RawTransport;
use Tests\Helpers\Stub;
use Tests\Helpers\StubTransport;


class HereNowTest extends \PubNubTestCase
{
    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testMultipleChannelState()
    {
        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1,ch2")
            ->withQuery([
                'state' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => Stub::ANY
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"total_occupancy\":3,\"total_channels\":2,\"channels\":{\"ch1\":{\"occupancy\":1,\"uuids\":[{\"uuid\":\"user1\"}]},\"ch2\":{\"occupancy\":2,\"uuids\":[{\"uuid\":\"user1\"},{\"uuid\":\"user3\"}]}}},\"service\":\"Presence\"}");

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
        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1,ch2")
            ->withQuery([
                'state' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => Stub::ANY
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"total_occupancy\":3,\"total_channels\":2,\"channels\":{\"ch1\":{\"occupancy\":1,\"uuids\":[{\"uuid\":\"user1\"}]},\"ch2\":{\"occupancy\":2,\"uuids\":[{\"uuid\":\"user1\"},{\"uuid\":\"user3\"}]}}},\"service\":\"Presence\"}");

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
        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/game1,game2")
            ->withQuery([
                'pnsdk' => $this->encodedSdkName,
                'uuid' => Stub::ANY
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": {\"game1\": {\"uuids\": [\"a3ffd012-a3b9-478c-8705-64089f24d71e\"], \"occupancy\": 1}}, \"total_channels\": 1, \"total_occupancy\": 1}, \"service\": \"Presence\"}");

        $response = $hereNow->channels(["game1", "game2"])->includeState(false)->sync();

        $this->assertEquals($response->getTotalChannels(), 1);
        $this->assertEquals($response->getTotalOccupancy(), 1);

        $this->assertEquals($response->getChannels()[0]->getChannelName(), "game1");
        $this->assertEquals($response->getChannels()[0]->getOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()[0]->getOccupants()), 1);
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getUuid(), "a3ffd012-a3b9-478c-8705-64089f24d71e");
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getState(), null);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testMultipleChannelWithoutStateUUIDs()
    {
        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/game1,game2")
            ->withQuery([
                'disable-uuids' => '1',
                'uuid' => Stub::ANY,
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": {\"game1\": {\"occupancy\": 1}}, \"total_channels\": 1, \"total_occupancy\": 1}, \"service\": \"Presence\"}");

        $response = $hereNow->channels(["game1", "game2"])->includeUuids(false)->includeState(false)->sync();

        $this->assertEquals($response->getTotalChannels(), 1);
        $this->assertEquals($response->getTotalOccupancy(), 1);

        $this->assertEquals($response->getChannels()[0]->getChannelName(), "game1");
        $this->assertEquals($response->getChannels()[0]->getOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()[0]->getOccupants()), null);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testSingularChannelWithoutStateUUIDs()
    {
        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/game1")
            ->withQuery([
                'disable-uuids' => '1',
                'uuid' => Stub::ANY,
                'pnsdk' => $this->encodedSdkName
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
        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/game1")
            ->withQuery([
                'uuid' => Stub::ANY,
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"service\": \"Presence\", \"uuids\": [\"a3ffd012-a3b9-478c-8705-64089f24d71e\"], \"occupancy\": 1}");

        $response = $hereNow->channels("game1")->includeState(false)->sync();

        $this->assertEquals($response->getTotalChannels(), 1);
        $this->assertEquals($response->getTotalOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()), 1);
        $this->assertEquals($response->getChannels()[0]->getOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()[0]->getOccupants()), 1);
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getUuid(), "a3ffd012-a3b9-478c-8705-64089f24d71e");
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getState(), null);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testSingularChannel()
    {
        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/game1")
            ->withQuery([
                'state' => '1',
                'uuid' => Stub::ANY,
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"service\":\"Presence\",\"uuids\":[{\"uuid\":\"a3ffd012-a3b9-478c-8705-64089f24d71e\",\"state\":{\"age\":10}}],\"occupancy\":1}");

        $response = $hereNow->channels("game1")->includeState(true)->sync();

        $this->assertEquals($response->getTotalChannels(), 1);
        $this->assertEquals($response->getTotalOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()), 1);
        $this->assertEquals($response->getChannels()[0]->getOccupancy(), 1);
        $this->assertEquals(count($response->getChannels()[0]->getOccupants()), 1);
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getUuid(), "a3ffd012-a3b9-478c-8705-64089f24d71e");
        $this->assertEquals($response->getChannels()[0]->getOccupants()[0]->getState(), ["age" => 10]);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testSingularChannelAndGroup()
    {
        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/game1")
            ->withQuery([
                'channel-group' => 'grp1',
                'state' => '1',
                'uuid' => Stub::ANY,
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"channels\":{}, \"total_channels\":0, \"total_occupancy\":0},\"service\":\"Presence\"}");

        $response = $hereNow->channelGroups("grp1")->channels("game1")->includeState(true)->sync();

        $this->assertEquals($response->getTotalOccupancy(), 0);
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testIsAuthRequiredSuccess()
    {
        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1,ch2")
            ->withQuery([
                'state' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => Stub::ANY,
                'auth' => 'myKey'
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"total_occupancy\":3,\"total_channels\":2,\"channels\":{\"ch1\":{\"occupancy\":1,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}}]},\"ch2\":{\"occupancy\":2,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}},{\"uuid\":\"user3\",\"state\":{\"age\":30}}]}}},\"service\":\"Presence\"}");

        $this->pubnub->getConfiguration()->setAuthKey("myKey");

        $hereNow->channels(["ch1", "ch2"])->includeState(true)->sync();
    }

    /**
     * @group herenow
     * @group herenow-integrational
     */
    public function testNullSubKey()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1,ch2")
            ->withQuery([
                'channel-group' => 'grp1',
                'state' => '1',
                'uuid' => Stub::ANY,
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"total_occupancy\":3,\"total_channels\":2,\"channels\":{\"ch1\":{\"occupancy\":1,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}}]},\"ch2\":{\"occupancy\":2,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}},{\"uuid\":\"user3\",\"state\":{\"age\":30}}]}}},\"service\":\"Presence\"}");

        $this->pubnub->getConfiguration()->setSubscribeKey(null);

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

        $hereNow = new HereNowExposed($this->pubnub);
        $hereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1,ch2")
            ->withQuery([
                'state' => '1',
                'pnsdk' => $this->encodedSdkName,
                'uuid' => Stub::ANY,
                'auth' => 'myKey'
            ])
            ->setResponseBody("{\"status\":200,\"message\":\"OK\",\"payload\":{\"total_occupancy\":3,\"total_channels\":2,\"channels\":{\"ch1\":{\"occupancy\":1,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}}]},\"ch2\":{\"occupancy\":2,\"uuids\":[{\"uuid\":\"user1\",\"state\":{\"age\":10}},{\"uuid\":\"user3\",\"state\":{\"age\":30}}]}}},\"service\":\"Presence\"}");

        $this->pubnub->getConfiguration()->setSubscribeKey("");

        $hereNow->channels(["ch1", "ch2"])->includeState(true)->sync();
    }

    public function testSuperCallTest()
    {
        // Not valid
        // ,~/
        $characters = "-._:?#[]@!$&'()*+;=`|";

        $res = $this->pubnub_pam->hereNow()
            ->channels($characters)
            ->sync();
    }
}


class HereNowExposed extends HereNow
{
    protected $transport;

    public function __construct($pubnubInstance)
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
