<?php

namespace PubNub\Endpoints;


use PubNub\Models\Consumer\PNStatus;
use PubNub\PubNub;
use Requests_Exception;

abstract class Endpoint
{
    /** @var  PubNub */
    private $pubnub;

    public function __construct($pubnubInstance)
    {
        $this->pubnub = $pubnubInstance;
    }

    abstract protected function validateParams();

    /**
     * @param array $json Decoded json
     * @return mixed
     */
    abstract protected function createResponse($json);

    abstract protected function getOperationType();

    abstract protected function isAuthRequired();

    abstract protected function buildPath();

    abstract protected function httpMethod();

    public function sync()
    {
        return $this->request()->getResult();
    }

    public function envelope()
    {
        return $this->request();
    }

    /**
     * @return Envelope
     */
    protected function request()
    {
        $headers = ['Accept' => 'application/json'];
        $url = $this->pubnub->getBasePath() . $this->buildPath();
        $data = null;
        $type = \Requests::GET;
        $options = [];

        try {
            $request = \Requests::request($url, $headers, $data, $type, $options);
        } catch (Requests_Exception $e) {
            // TODO: build exception
            return new Envelope(null, null);
        }

        if ($request->status_code == 200) {
            $parsedJSON = json_decode($request->body);

            return new Envelope($this->createResponse($parsedJSON), $this->createStatus());
        } else {
            return new Envelope(null, null);
        }
    }


    protected function createBaseParams()
    {
        $params = [];

        $params['pnsdk'] = "PubNub-PHP/" . $this->pubnub->getVersion();
        $params['uuid'] = $this->pubnub->getConfiguration()->getUuid();

        // TODO: check for instance identifier
        // TODO: check for request identifier

        if ($this->isAuthRequired() && $this->pubnub->getConfiguration()->getAuthKey()) {
            $params['auth'] = $this->pubnub->getConfiguration()->getAuthKey();
        }
    }

    private function createStatus()
    {
        $status = new PNStatus();

        return $status;
    }

    protected function getAffectedChannels()
    {
        return null;
    }

    protected function getAffectedChannelGroups()
    {
        return null;
    }
}


class Envelope
{
    private $state;
    private $result;

    /**
     * Envelope constructor.
     *
     * @param $state
     * @param $result
     */
    public function __construct($result, $state)
    {
        $this->state = $state;
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}