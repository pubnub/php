<?php

use PubNub\Endpoints\Presence\HereNow;

class HereNowTest extends PubNubTestCase
{
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
}

class HereNowExposed extends HereNow
{
    /** @var  RawTransport */
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
