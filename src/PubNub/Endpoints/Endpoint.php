<?php

namespace PubNub\Endpoints;

use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubException;
use PubNub\Exceptions\PubNubConnectionException;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\ResponseHelpers\PNEnvelope;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\Models\ResponseHelpers\ResponseInfo;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use Requests_Exception;

abstract class Endpoint
{
    /** @var  PubNub */
    protected $pubnub;

    /** @var  PNEnvelope */
    protected $envelope;

    public function __construct(PubNub $pubnubInstance)
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
     * @return int
     */
    abstract protected function getOperationType();

    /**
     * @return bool
     */
    abstract protected function isAuthRequired();

    /**
     * @return null|string
     */
    abstract protected function buildData();

    /**
     * @return string
     */
    abstract protected function buildPath();

    /**
     * @return array
     */
    abstract protected function customParams();

    /**
     * @return int
     */
    abstract protected function getRequestTimeout();

    /**
     * @return int
     */
    abstract protected function getConnectTimeout();

    /**
     * @return string PNHttpMethod
     */
    abstract protected function httpMethod();

    /**
     * @throws PubNubValidationException
     */
    protected function validateSubscribeKey()
    {
        $subscribeKey = $this->pubnub->getConfiguration()->getSubscribeKey();

        if ($subscribeKey == null || empty($subscribeKey)) {
            throw new PubNubValidationException("Subscribe Key not configured");
        }
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validatePublishKey()
    {
        $publishKey = $this->pubnub->getConfiguration()->getPublishKey();

        if ($publishKey == null || empty($publishKey)) {
            throw new PubNubValidationException("Publish Key not configured");
        }
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateSecretKey()
    {
        $secretKey = $this->pubnub->getConfiguration()->getSecretKey();

        if ($secretKey === null || empty($secretKey)) {
            throw new PubNubValidationException("Secret key not configured");
        }
    }

    /**
     * @param string[]|string $channels
     * @param string[]|string $groups
     * @throws PubNubValidationException
     */
    protected function validateChannelGroups($channels, $groups)
    {
        if (count($channels) === 0 && count($groups) === 0) {
            throw new PubNubValidationException("Channel or group missing");
        }
    }

    /**
     * @return array
     */
    protected function defaultParams()
    {
        $params = [];
        $config = $this->pubnub->getConfiguration();

        $params['pnsdk'] = "PubNub-PHP/" . $this->pubnub->getSdkVersion();
        $params['uuid'] = $config->getUuid();

        // TODO: check for instance identifier
        // TODO: check for request identifier

        if ($this->isAuthRequired() && $config->getAuthKey()) {
            $params['auth'] = $config->getAuthKey();
        }

        return $params;
    }

    /**
     * Params build flow: signed <- custom <- default
     *
     * @return array
     */
    protected function buildParams()
    {
        $params = array_merge($this->customParams(), $this->defaultParams());
        $config = $this->pubnub->getConfiguration();

        if ($config->getSecretKey()) {
            $params['timestamp'] = (string) $this->pubnub->timestamp();
            $signedInput = $config->getSubscribeKey() . "\n" . $config->getPublishKey() . "\n";

            if ($this->getOperationType() == PNOperationType::PNAccessManagerGrant ||
                $this->getOperationType() == PNOperationType::PNAccessManagerRevoke) {
                $signedInput .= "grant\n";
            } else if ($this->getOperationType() === PNOperationType::PNAccessManagerAudit) {
                $signedInput .= "audit\n";
            } else {
                $signedInput .= $this->buildPath() . "\n";
            }

            $signedInput .= PubNubUtil::preparePamParams($params);

            $params['signature'] = PubNubUtil::signSha256(
                $this->pubnub->getConfiguration()->getSecretKey(),
                $signedInput
            );
        }

        if ($this->getOperationType() == PNOperationType::PNSetStateOperation
            && array_key_exists('state', $this->customParams())) {
            $params['state'] = PubNubUtil::urlEncode($params['state']);
        }

        $params['pnsdk'] = PubNubUtil::urlEncode($params['pnsdk']);
        // TODO: publish meta should be encoded here
        // TODO: pnsdk should be reassigned here
        return $params;
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
     * @return array
     */
    protected function requestOptions() {
        $options = [
            'timeout' => $this->getRequestTimeout(),
            'connect_timeout' => $this->getConnectTimeout()
        ];

        return $options;
    }

    /**
     * @return PNEnvelope
     */
    protected function invokeRequest()
    {
        $headers = ['Accept' => 'application/json'];

        $url = PubNubUtil::buildUrl(
            $this->pubnub->getBasePath(),
            $this->buildPath(),
            $this->buildParams()
        );
        $data = $this->buildData();
        $type = \Requests::GET;
        $options = $this->requestOptions();

        if ($this->httpMethod() == PNHttpMethod::POST) {
            $type = \Requests::POST;
        }

        // TODO: log url
//        print_r("URL: " . $url . "\n");
        $statusCategory = PNStatusCategory::PNUnknownCategory;

        try {
            $request = \Requests::request($url, $headers, $data, $type, $options);
        } catch (\Requests_Exception_HTTP_Unknown $e) {
            // TODO: build exception
            return new PNEnvelope($e->getData(), $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($e)
            ));
        } catch (\Requests_Exception_Transport_cURL  $e) {
            // TODO: build exception
            return new PNEnvelope($e->getData(), $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($e)
            ));
        } catch (\Requests_Exception_HTTP $e) {
            // TODO: build exception
            return new PNEnvelope($e->getData(), $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($e)
            ));
        } catch (Requests_Exception $e) {
            if ($e->getType() === 'curlerror' && strpos($e->getMessage(), "cURL error 28") === 0) {
                $statusCategory = PNStatusCategory::PNTimeoutCategory;
            }
            return new PNEnvelope(null, $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($e)
            ));
        } catch (\Exception $e) {
            // TODO: build exception
            return new PNEnvelope(null, $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($e)
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
            // NOTICE: 1 == JSON_OBJECT_AS_ARRAY
            $parsedJSON = json_decode($request->body, true, 512, 1);

            if (json_last_error()) {
                return new PNEnvelope(null, $this->createStatus(
                    $statusCategory,
                    $request->body,
                    $responseInfo,
                    (new PubNubResponseParsingException())
                        ->setResponseString($request->body)
                        ->setDescription(json_last_error_msg())
                ));
            }

            return new PNEnvelope($this->createResponse($parsedJSON),
                $this->createStatus($statusCategory, $request->body, $responseInfo, null)
            );
        } else {
            switch ($request->status_code) {
                case 400:
                    $statusCategory = PNStatusCategory::PNBadRequestCategory;
                    break;
                case 403:
                    $statusCategory = PNStatusCategory::PNAccessDeniedCategory;
                    break;
            }

            $exception = (new PubNubServerException())
                ->setStatusCode($request->status_code)
                ->setRawBody($request->body);

            // NOTICE: 530 code is for testing purposes only
            if ($request->status_code === 530) {
                $exception->forceMessage($request->body);
            }

            return new PNEnvelope(
                null,
                $this->createStatus($statusCategory, $request->body, $responseInfo, $exception)
            );
        }
    }

    /**
     * @param int{PNStatusCategory::PNUnknownCategory..PNStatusCategory::PNRequestMessageCountExceededCategory} $category
     * @param $response
     * @param ResponseInfo $responseInfo
     * @param PubNubException | null $exception
     * @return PNStatus
     */
    private function createStatus($category, $response, ResponseInfo $responseInfo, $exception)
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

    /**
     * @param $json
     * @return array
     * @throws PubNubResponseParsingException
     */
    protected static function fetchPayload($json) {
        if (!array_key_exists('payload', $json)) {
            throw (new PubNubResponseParsingException())->setDescription("No payload found in response");
        } else {
            return $json['payload'];
        }
    }
}
