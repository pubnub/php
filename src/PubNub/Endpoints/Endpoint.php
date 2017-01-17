<?php

namespace PubNub\Endpoints;


use Enums\PNOperationTypes;
use PubNub\Builders\PubNubErrorBuilder;
use PubNub\Enums\PNHttpMethod;
use PubNub\Models\Consumer\PNStatus;
use PubNub\PubNub;
use PubNub\PubNubException;
use PubNub\PubNubUtil;
use Requests_Exception;

abstract class Endpoint
{
    /** @var  PubNub */
    protected $pubnub;

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

    /**
     * @return PNOperationTypes
     */
    abstract protected function getOperationType();

    /**
     * @return bool
     */
    abstract protected function isAuthRequired();

    /**
     * @return string
     */
    abstract protected function buildPath();

    /**
     * @return array
     */
    abstract protected function buildParams();

    /**
     * @return PNHttpMethod
     */
    abstract protected function httpMethod();

    protected function validateSubscribeKey()
    {
        $subscribeKey = $this->pubnub->getConfiguration()->getSubscribeKey();

        if ($subscribeKey == null || empty($subscribeKey)) {
            throw (new PubNubException())->setPubnubError(PubNubErrorBuilder::predefined()->PNERROBJ_SUBSCRIBE_KEY_MISSING);
        }
    }

    protected function validatePublishKey()
    {
        $publishKey = $this->pubnub->getConfiguration()->getPublishKey();

        if ($publishKey == null || empty($publishKey)) {
            throw (new PubNubException())->setPubnubError(PubNubErrorBuilder::predefined()->PNERROBJ_PUBLISH_KEY_MISSING);
        }
    }

    public function sync()
    {
        $this->validateParams();

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
        $url = PubNubUtil::buildUrl($this->pubnub->getBasePath(), $this->buildPath(), $this->buildParams());
        $data = null;
        $type = \Requests::GET;
        $options = [];

        if ($this->httpMethod() == PNHttpMethod::POST) {
            $type = \Requests::POST;
        }

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

    /**
     * @return array
     */
    protected function defaultParams()
    {
        $params = [];

        $params['pnsdk'] = "PubNub-PHP/" . $this->pubnub->getVersion();
        $params['uuid'] = $this->pubnub->getConfiguration()->getUuid();

        // TODO: check for instance identifier
        // TODO: check for request identifier

        if ($this->isAuthRequired() && $this->pubnub->getConfiguration()->getAuthKey()) {
            $params['auth'] = $this->pubnub->getConfiguration()->getAuthKey();
        }

        return $params;
    }

    /**
     * @return PNStatus
     */
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