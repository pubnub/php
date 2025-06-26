<?php

namespace PubNub\Endpoints;

use Exception;
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
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Endpoint
{
    protected bool $endpointAuthRequired;
    protected int $endpointConnectTimeout;
    protected int $endpointRequestTimeout;
    protected string $endpointHttpMethod;
    protected int $endpointOperationType;
    protected string $endpointName;

    /** @var string[] */
    protected array $customHeaders = [];

    protected const RESPONSE_IS_JSON = true;

    /** @var  PubNub */
    protected $pubnub;

    /** @var  PNEnvelope */
    protected $envelope;

    protected $customHost = null;

    protected $followRedirects = true;

    public function __construct(PubNub $pubnubInstance)
    {
        $this->pubnub = $pubnubInstance;
    }

    abstract protected function validateParams();

    /**
     * @param array $result
     * @return mixed
     */
    abstract protected function createResponse($result);

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return $this->endpointOperationType;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return $this->endpointAuthRequired;
    }

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
    protected function getRequestTimeout()
    {
        return $this->endpointRequestTimeout;
    }

    /**
     * @return int
     */
    protected function getConnectTimeout()
    {
        return $this->endpointConnectTimeout;
    }

    /**
     * @return string PNHttpMethod
     */
    protected function httpMethod()
    {
        return $this->endpointHttpMethod;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return !empty($this->endpointName) ? $this->endpointName : substr(strrchr(get_class($this), '\\'), 1);
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateSubscribeKey()
    {
        $subscribeKey = $this->pubnub->getConfiguration()->getSubscribeKey();

        if (strlen($subscribeKey) === 0) {
            throw new PubNubValidationException("Subscribe Key not configured");
        }
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validatePublishKey()
    {
        $publishKey = $this->pubnub->getConfiguration()->getPublishKey();

        if (strlen($publishKey) === 0) {
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

    protected function defaultHeaders()
    {
        return ['Accept' => 'application/json', 'Connection' => 'Keep-Alive'];
    }

    protected function customHeaders()
    {
        return $this->customHeaders;
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

        if ($this->isAuthRequired()) {
            $token = $this->pubnub->getToken();
            if ($token) {
                $params['auth'] = $token;
            } elseif ($config->getAuthKey()) {
                $params['auth'] = $config->getAuthKey();
            }
        }

        if (!!$this->pubnub->getTelemetryManager()) {
            foreach ($this->pubnub->getTelemetryManager()->operationLatencies() as $queryName => $queryParam) {
                $params[$queryName] = PubNubUtil::urlEncode($queryParam);
            }
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
            $httpMethod = $this->httpMethod();

            if ($this->getName() == "Publish") {
                // This is because of a server-side bug, old publish using post should be deprecated
                $httpMethod = PNHttpMethod::GET;
            }

            $params['timestamp'] = (string) $this->pubnub->timestamp();

            ksort($params);

            $signedInput = $httpMethod
                . "\n"
                . $config->getPublishKey()
                . "\n"
                . $this->buildPath()
                . "\n"
                . PubNubUtil::preparePamParams($params)
                . "\n";

            if (PNHttpMethod::POST == $httpMethod || PNHttpMethod::PATCH == $httpMethod) {
                $signedInput .= $this->buildData();
            }

            $signature = 'v2.' . PubNubUtil::signSha256(
                $this->pubnub->getConfiguration()->getSecretKey(),
                $signedInput
            );

            $signature = preg_replace('/=+$/', '', $signature);

            $params['signature'] = $signature;
        }

        if (
            $this->getOperationType() == PNOperationType::PNPublishOperation
            && array_key_exists('meta', $this->customParams())
        ) {
            $params['meta'] = PubNubUtil::urlEncode($params['meta']);
        }

        if (
            $this->getOperationType() == PNOperationType::PNSetStateOperation
            && array_key_exists('state', $this->customParams())
        ) {
            $params['state'] = PubNubUtil::urlEncode($params['state']);
        }

        if (array_key_exists('pnsdk', $params)) {
            $params['pnsdk'] = PubNubUtil::urlEncode($params['pnsdk']);
        }

        if (array_key_exists('uuid', $params)) {
            $params['uuid'] = PubNubUtil::urlEncode($params['uuid']);
        }

        if (array_key_exists('auth', $params)) {
            $params['auth'] = PubNubUtil::urlEncode($params['auth']);
        }

        if (array_key_exists('channel', $params)) {
            $params['channel'] = PubNubUtil::urlEncode($params['channel']);
        }

        if (array_key_exists('channel-group', $params)) {
            $params['channel-group'] = PubNubUtil::urlEncode($params['channel-group']);
        }

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
            $this->envelope = $this->sendRequest($this->getRequest());
        }

        return $this->envelope;
    }

    /**
     * @return PNEnvelope
     */
    protected function sendRequest(RequestInterface $request): PNEnvelope
    {
        $client = $this->pubnub->getClient();

        try {
            if (is_callable([$client, 'send'])) {
                $response = $client->send($request, $this->requestOptions());
            } else {
                $response = $client->sendRequest($request);
            }
            $envelope = $this->parseResponse($response);
        } catch (NetworkExceptionInterface $exception) {
            return new PNEnvelope(null, $this->createStatus(
                PNStatusCategory::PNTimeoutCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($exception)
            ));
        } catch (ClientExceptionInterface $exception) {
            $statusCode = $exception->getCode();
            $response = substr($exception->getMessage(), strpos($exception->getMessage(), "\n") + 1);
            $pnServerException = new PubNubServerException();
            if (is_callable([$exception, 'getResponse'])) {
                $response = $exception->getResponse()->getBody()->getContents();
            } else {
                $response = substr($exception->getMessage(), strpos($exception->getMessage(), "\n") + 1);
            }
            $pnServerException->setRawBody($response);
            $pnServerException->setStatusCode($exception->getCode());

            $uuid = null;
            $authKey = null;

            parse_str($request->getUri()->getQuery(), $query);

            if (array_key_exists('uuid', $query) && strlen($query['uuid']) > 0) {
                $uuid = $query['uuid'];
            }

            if (array_key_exists('auth', $query) && strlen($query['auth']) > 0) {
                $authKey = $query['auth'];
            }

            $responseInfo = new ResponseInfo(
                $statusCode,
                $request->getUri()->getScheme() === 'https',
                $request->getUri()->getHost(),
                $uuid,
                $authKey,
                null
            );
            $statusCategory = PNStatusCategory::PNBadRequestCategory;
            return new PNEnvelope(null, $this->createStatus($statusCategory, null, $responseInfo, $pnServerException));
        }
        return $envelope;
    }

    /**
     * @param ResponseInterface $response
     * @return PNEnvelope
     */
    public function parseResponse(ResponseInterface $response): PNEnvelope
    {
        $result = null;
        $status = null;

        $statusCode = $response->getStatusCode();
        $statusCategory = PNStatusCategory::PNUnknownCategory;

        $request = $this->getRequest();

        $uuid = null;
        $authKey = null;

        parse_str($request->getUri()->getQuery(), $query);

        if (array_key_exists('uuid', $query) && strlen($query['uuid']) > 0) {
            $uuid = $query['uuid'];
        }

        if (array_key_exists('auth', $query) && strlen($query['auth']) > 0) {
            $authKey = $query['auth'];
        }
        $responseInfo = new ResponseInfo(
            $statusCode,
            $request->getUri()->getScheme() === 'https',
            $request->getUri()->getHost(),
            $uuid,
            $authKey,
            $response
        );

        if ($statusCode === 200) {
            $contents = $response->getBody()->getContents();
            if (static::RESPONSE_IS_JSON) {
                $parsedJSON = json_decode($contents, true);

                if (json_last_error()) {
                    return new PNEnvelope(null, $this->createStatus(
                        $statusCategory,
                        $response->getBody()->getContents(),
                        $responseInfo,
                        (new PubNubResponseParsingException())
                            ->setResponseString($request->getBody())
                            ->setDescription(json_last_error_msg())
                    ));
                }
                $result = $this->createResponse($parsedJSON);
            } else {
                $result = $this->createResponse($contents);
            }
            $status = $this->createStatus($statusCategory, $response->getBody(), $responseInfo, null);
        } elseif ($statusCode === 307 && !$this->followRedirects) {
            $result = $this->createResponse($response->getHeaders());
            $status = $this->createStatus($statusCategory, $response->getBody(), $responseInfo, null);
        } else {
            $result = null;
            switch ($statusCode) {
                case 400:
                    $statusCategory = PNStatusCategory::PNBadRequestCategory;
                    break;
                case 403:
                    $statusCategory = PNStatusCategory::PNAccessDeniedCategory;
                    break;
            }

            $exception = (new PubNubServerException())
                ->setStatusCode($statusCode)
                ->setRawBody($response->getBody());

            $status = $this->createStatus($statusCategory, $response->getBody(), $responseInfo, $exception);
        }

        return new PNEnvelope($result, $status);
    }

    /**
     * @return array
     */
    protected function requestOptions()
    {
        return [
            'timeout' => $this->getRequestTimeout(),
            'connect_timeout' => $this->getConnectTimeout(),
            'useragent' => 'PHP/' . PHP_VERSION,
            'allow_redirects' => (bool)$this->followRedirects,
            'version' => '2',
        ];
    }

    public function getRequest(): RequestInterface
    {
        $factory = $this->pubnub->getRequestFactory();
        $method = $this->httpMethod();
        $url =  PubNubUtil::buildUrl(
            $this->pubnub->getBasePath($this->customHost),
            $this->buildPath(),
            $this->buildParams()
        );

        $data = $this->buildData();
        $method = $this->httpMethod();
        $headers = array_merge($this->defaultHeaders(), $this->customHeaders());

        $request = $factory->createRequest($method, $url);

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        if ($data) {
            $request = $request->withBody(\GuzzleHttp\Psr7\Utils::streamFor($data));
        }
        return $request;
    }

    /**
     * @param int $category
     * @param $response
     * @param ResponseInfo | null $responseInfo
     * @param PubNubException | null $exception
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
        $pnStatus->setAffectedUsers($this->getAffectedUsers());

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

    protected function getAffectedUsers()
    {
        return null;
    }

    /**
     * @param $json
     * @return array
     * @throws PubNubResponseParsingException
     */
    protected static function fetchPayload($json)
    {
        if (!boolval($json)) {
            throw (new PubNubResponseParsingException())->setDescription("Body cannot be null");
        }

        if (!array_key_exists('payload', $json)) {
            throw (new PubNubResponseParsingException())->setDescription("No payload found in response");
        } else {
            return $json['payload'];
        }
    }
}
