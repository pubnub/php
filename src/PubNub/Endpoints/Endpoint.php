<?php

namespace PubNub\Endpoints;


use PubNub\Builders\PubNubErrorBuilder;
use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNStatusCategory;
use PubNub\Models\ResponseHelpers\PNEnvelope;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\Models\ResponseHelpers\ResponseInfo;
use PubNub\PubNub;
use PubNub\PubNubException;
use PubNub\PubNubUtil;
use Requests_Exception;

abstract class Endpoint
{
    /** @var  PubNub */
    protected $pubnub;

    /** @var  PNEnvelope */
    protected $envelope;

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
     * @return PNOperationType
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

    /**
     * Return a Result only.
     * Errors are thrown explicitly, so catch them with try/catch block
     *
     * @return mixed
     * @throws PubNubException
     */
    public function sync()
    {
        $this->validateParams();

        $envelope = $this->invokeRequestAndCacheIt();

        if ($envelope->isError()) {
            throw $envelope->getStatus()->getException();
        }

        return $envelope->getResult();
    }

    /**
     * Returns an Envelope that contains both result and status.
     * All Errors are wrapped, so no need to use try/catch blocks
     *
     * @return PNEnvelope
     */
    public function envelope()
    {
        return $this->invokeRequestAndCacheIt();
    }

    /**
     * Clear cached envelope
     */
    public function clear()
    {
        $this->envelope = null;
    }

    /**
     * @return PNEnvelope
     * @throws PubNubException
     */
    protected function invokeRequestAndCacheIt()
    {
        if ($this->envelope === null) {
            $this->envelope = $this->invokeRequest();
        }

        return $this->envelope;
    }

    /**
     * @return PNEnvelope
     */
    protected function invokeRequest()
    {
        $headers = ['Accept' => 'application/json'];
        $url = PubNubUtil::buildUrl($this->pubnub->getBasePath(), $this->buildPath(), $this->buildParams());
        $data = null;
        $type = \Requests::GET;
        $options = [];

        if ($this->httpMethod() == PNHttpMethod::POST) {
            $type = \Requests::POST;
        }

        $statusCategory = PNStatusCategory::PNUnknownCategory;

        try {
            $request = \Requests::request($url, $headers, $data, $type, $options);
        } catch (\Requests_Exception_HTTP_Unknown $e) {
            // TODO: build exception
            return new PNEnvelope($e->getData(), $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubException())->setPubnubError(PubNubErrorBuilder::predefined()->PNERROBJ_CHANNEL_MISSING)
            ));
        } catch (\Requests_Exception_HTTP $e) {
            // TODO: build exception
            return new PNEnvelope($e->getData(), $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubException())->setPubnubError(PubNubErrorBuilder::predefined()->PNERROBJ_CHANNEL_MISSING)
            ));
        } catch (Requests_Exception $e) {
            // TODO: build exception
            return new PNEnvelope($e->getData(), $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubException())
                    ->setPubnubError(PubNubErrorBuilder::predefined()->UNEXPECTED_REQUESTS_EXCEPTION
                        ->setMessage($e->getMessage())
                    )
                    ->setStatusCode($e->getCode())
            ));
        }

        $url = parse_url($url);
        $query = [];
        parse_str($url['query'], $query);
        $uuid = null;
        $authKey = null;

        if (key_exists('uuid', $query) && strlen($query['uuid']) > 0) {
            $uuid = $query['uuid'];
        }

        if (key_exists('auth', $query) && strlen($query['auth']) > 0) {
            $uuid = $query['auth'];
        }

        $responseInfo = new ResponseInfo(
            $request->status_code,
            $url['scheme'] == 'https',
            $url['host'],
            $uuid,
            $authKey,
            $request
        );

        if ($request->status_code == 200) {
            $parsedJSON = json_decode($request->body);

            return new PNEnvelope($this->createResponse($parsedJSON),
                $this->createStatus($statusCategory, $request->body, $responseInfo, null)
            );
        } else {
            $exception = null;

            return new PNEnvelope(null, $this->createStatus($statusCategory, $request->body, $responseInfo, $exception));
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
     * @param int{PNStatusCategory::PNUnknownCategory..PNStatusCategory::PNRequestMessageCountExceededCategory} $category
     * @param $response
     * @param ResponseInfo $responseInfo
     * @param PubNubException $exception
     * @return PNStatus
     */
    private function createStatus($category, $response, $responseInfo, $exception)
    {
        $pnStatus = new PNStatus();

        if ($response !== null) {
            $pnStatus->setOriginalResponse($response);
        }

        if ($exception !== null) {
            $pnStatus->setException($exception);
        }

        if ($responseInfo !== null) {
            $pnStatus->setStatusCode($responseInfo->getStatusCode());
            $pnStatus->setTlsEnabled($responseInfo->isTlsEnabled());
            $pnStatus->setOrigin($responseInfo->getOrigin());
            $pnStatus->setUuid($responseInfo->getUuid());
            $pnStatus->setAuthKey($responseInfo->getAuthKey());
        }

        $pnStatus->setOperation($this->getOperationType());
        $pnStatus->setCategory($category);
        $pnStatus->setAffectedChannels($this->getAffectedChannels());
        $pnStatus->setAffectedChannelGroups($this->getAffectedChannelGroups());

        return $pnStatus;
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
