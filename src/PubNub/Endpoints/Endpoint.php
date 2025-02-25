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
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WpOrg\Requests\Requests;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Exception\Transport\Curl as RequestsTransportCurlException;
use WpOrg\Requests\Exception\Http\StatusUnknown as ReuestsHttpStatusUnknownException;
use WpOrg\Requests\Exception\Http as RequestsHttpException;
use WpOrg\Requests\Transport\Curl;
use WpOrg\Requests\Transport\Fsockopen;

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

    /** @var array */
    protected static $cachedTransports = [];

    protected $followRedirects = true;

    public function __construct(PubNub $pubnubInstance)
    {
        $this->pubnub = $pubnubInstance;
    }

    abstract protected function validateParams();

    /**
     * @param array $result Decoded json
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
            // $this->envelope = $this->invokeRequest();
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
            $response = $client->sendRequest($request);
            $envelope = $this->parseResponse($response);
        } catch (NetworkExceptionInterface $exception) {
            return new PNEnvelope(null, $this->createStatus(
                PNStatusCategory::PNTimeoutCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($exception)
            ));
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
            $uuid = $query['auth'];
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
                            ->setResponseString($request->body)
                            ->setDescription(json_last_error_msg())
                    ));
                }
                $result = $this->createResponse($parsedJSON);
            } else {
                $result = $this->createResponse($contents);
            }
            $status = $this->createStatus($statusCategory, $response->getBody(), $responseInfo, null);
        } elseif ($statusCode === 307 && !$this->followRedirects) {
            $result = $this->createResponse($response);
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
            'transport' => $this->getTransport(),
            'useragent' => 'PHP/' . PHP_VERSION,
            'follow_redirects' => $this->followRedirects,
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
        $options = $this->requestOptions();
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

    protected function getTransport()
    {
        return $this->pubnub->getConfiguration()->getTransport() ?? $this->getDefaultTransport();
    }

    /**
     * @return PNEnvelope
     */
    protected function invokeRequest()
    {
        $headers = array_merge($this->defaultHeaders(), $this->customHeaders());

        $url = PubNubUtil::buildUrl(
            $this->pubnub->getBasePath($this->customHost),
            $this->buildPath(),
            $this->buildParams()
        );
        $data = $this->buildData();
        $method = $this->httpMethod();
        $options = $this->requestOptions();

        $this->pubnub->getLogger()->debug($method . " " . $url, ['method' => $this->getName()]);

        if ($data) {
            $this->pubnub->getLogger()->debug("Body:\n" . $data, ['method' => $this->getName()]);
        }

        $statusCategory = PNStatusCategory::PNUnknownCategory;
        $requestTimeStart = microtime(true);

        try {
            $request = Requests::request($url, $headers, $data, $method, $options);
        } catch (ReuestsHttpStatusUnknownException $e) {
            $this->pubnub->getLogger()->error($e->getMessage(), ['method' => $this->getName()]);

            return new PNEnvelope($e->getData(), $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($e)
            ));
        } catch (RequestsTransportCurlException $e) {
            $this->pubnub->getLogger()->error($e->getMessage(), ['method' => $this->getName()]);

            return new PNEnvelope($e->getData(), $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($e)
            ));
        } catch (RequestsHttpException $e) {
            $this->pubnub->getLogger()->error($e->getMessage(), ['method' => $this->getName()]);

            return new PNEnvelope($e->getData(), $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($e)
            ));
        } catch (RequestsException $e) {
            if ($e->getType() === 'curlerror' && strpos($e->getMessage(), "cURL error 28") === 0) {
                $statusCategory = PNStatusCategory::PNTimeoutCategory;
            }

            $this->pubnub->getLogger()->error($e->getMessage(), ['method' => $this->getName()]);

            return new PNEnvelope(null, $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($e)
            ));
        } catch (\Exception $e) {
            $this->pubnub->getLogger()->error($e->getMessage(), ['method' => $this->getName()]);

            return new PNEnvelope(null, $this->createStatus(
                $statusCategory,
                null,
                null,
                (new PubNubConnectionException())->setOriginalException($e)
            ));
        }

        $url = parse_url($url);
        $query = [];

        if (array_key_exists('query', $url)) {
            parse_str($url['query'], $query);
        }

        $uuid = null;
        $authKey = null;


        if (array_key_exists('uuid', $query) && strlen($query['uuid']) > 0) {
            $uuid = $query['uuid'];
        }

        if (array_key_exists('auth', $query) && strlen($query['auth']) > 0) {
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
            $requestTimeEnd = microtime(true);

            if (!!$this->pubnub->getTelemetryManager()) {
                $this->pubnub->getTelemetryManager()->cleanUpTelemetryData();
                $this->pubnub->getTelemetryManager()->storeLatency(
                    $requestTimeEnd - $requestTimeStart,
                    $this->getOperationType()
                );
            }

            $this->pubnub->getLogger()->debug(
                "Response body: " . $request->body,
                ['method' => $this->getName(), 'statusCode' => $request->status_code]
            );

            if (static::RESPONSE_IS_JSON) {
                // NOTICE: 1 == JSON_OBJECT_AS_ARRAY (hhvm doesn't support this constant)
                $parsedJSON = json_decode($request->body, true, 512, 1);
                $errorMessage = json_last_error_msg();

                if (json_last_error()) {
                    $this->pubnub->getLogger()->error(
                        "Unable to decode JSON body: " . $request->body,
                        ['method' => $this->getName()]
                    );

                    return new PNEnvelope(null, $this->createStatus(
                        $statusCategory,
                        $request->body,
                        $responseInfo,
                        (new PubNubResponseParsingException())
                            ->setResponseString($request->body)
                            ->setDescription($errorMessage)
                    ));
                }

                return new PNEnvelope(
                    $this->createResponse($parsedJSON),
                    $this->createStatus($statusCategory, $request->body, $responseInfo, null)
                );
            } else {
                return new PNEnvelope(
                    $this->createResponse($request->body),
                    $this->createStatus($statusCategory, $request->body, $responseInfo, null)
                );
            }
        } elseif ($request->status_code === 307 && !$this->followRedirects) {
            return new PNEnvelope(
                $this->createResponse($request),
                $this->createStatus($statusCategory, $request->body, $responseInfo, null)
            );
        } else {
            $this->pubnub->getLogger()->warning(
                "Response error: " . $request->body,
                ['method' => $this->getName(),
                'statusCode' => $request->status_code]
            );

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
     * @param int $category
     * @param $response
     * @param ResponseInfo | null $responseInfo
     * @param PubNubException | null $exception
     * @return PNStatus
     */
    private function createStatus($category, $response, $responseInfo, $exception, $errorMessage = null)
    {
        $pnStatus = new PNStatus();

        if ($response !== null) {
            $pnStatus->setOriginalResponse($response);
        }

        if ($exception !== null) {
            $pnStatus->setException($exception);
        }

        if ($errorMessage !== null) {
            $pnStatus->setErrorMessage($errorMessage);
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
     * @return \WpOrg\Requests\Transport
     * @throws \Exception
     */
    private function getDefaultTransport()
    {
        $need_ssl = (0 === stripos($this->pubnub->getBasePath($this->customHost), 'https://'));
        $capabilities = array('ssl' => $need_ssl);

        $cap_string = serialize($capabilities);
        $method = $this->httpMethod();

        if (!isset(self::$cachedTransports[$method])) {
            self::$cachedTransports[$method] = [];
        }

        if (isset(self::$cachedTransports[$method][$cap_string])) {
            return self::$cachedTransports[$method][$cap_string];
        }

        $transports = array(Curl::class, Fsockopen::class);

        foreach ($transports as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $result = call_user_func(array($class, 'test'), $capabilities);
            if ($result) {
                self::$cachedTransports[$method][$cap_string] = new $class();
                break;
            }
        }

        if (self::$cachedTransports[$method][$cap_string] === null) {
            throw new \Exception('No working transports found');
        }

        return self::$cachedTransports[$method][$cap_string];
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
