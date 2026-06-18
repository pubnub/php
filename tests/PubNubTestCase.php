<?php

use PHPUnit\Framework\TestCase;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;

// phpcs:ignore PSR1.Classes.ClassDeclaration
abstract class PubNubTestCase extends TestCase
{
    protected const CIPHER_KEY = "enigma";

    protected const SPECIAL_CHARACTERS = "-.,_~:/?#[]@!$&'()*+;=`|";
    protected const SPECIAL_CHANNEL = "-._~:/?#[]@!$&'()*+;=`|";

    /** @var Pubnub pubnub */
    protected $pubnub;

    /** @var PubNub pubnub_enc */
    protected $pubnub_enc;

    /** @var PubNub pubnub_pam */
    protected $pubnub_pam;

    /** @var PubNub pubnub_demo */
    protected $pubnub_demo;

    /** @var PNConfiguration config */
    protected $config;

    /** @var PNConfiguration config_demo */
    protected $config_demo;

    /** @var PNConfiguration config_enc */
    protected $config_enc;

    /** @var PNConfiguration config_pam */
    protected $config_pam;

    /** @var  string */
    protected $encodedSdkName;

    protected function fakeSignature($params, $httpMethod, $timestamp, $publishKey, $path, $secretKey)
    {

        $params['timestamp'] = (string) $timestamp;

        ksort($params);

        $signedInput = $httpMethod
            . "\n"
            . $publishKey
            . "\n"
            . $path
            . "\n"
            . PubNubUtil::preparePamParams($params)
            . "\n";

        $signature = 'v2.' . PubNubUtil::signSha256(
            $secretKey,
            $signedInput
        );

        $signature = preg_replace('/=+$/', '', $signature);

        return $signature;
    }

    public function setUp(): void
    {
        $publishKey = getenv("PUBLISH_KEY") ?: "";
        $subscribeKey = getenv("SUBSCRIBE_KEY") ?: "";
        $publishKeyPam = getenv("PUBLISH_PAM_KEY") ?: "";
        $subscribeKeyPam = getenv("SUBSCRIBE_PAM_KEY") ?: "";
        $secretKeyPam = getenv("SECRET_PAM_KEY") ?: "";
        $uuidMock = getenv("UUID_MOCK") ?: "UUID_MOCK";

        $logger = new Logger('PubNub');
        // Log at DEBUG so the SDK's per-request debug lines (endpoint, redacted
        // URL, negotiated HTTP version — see Endpoint::sendRequest) surface in
        // the test output. Logger::DEBUG is valid in both Monolog 2 and 3.
        $logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::DEBUG));

        parent::setUp();

        // The Balancer rate-limits dense publish bursts by holding the connection
        // ~10s before returning a 429 (see .claude/features/cicd-publish-timeout-
        // investigation.md). The SDK's default 10s non-subscribe timeout fires ~8ms
        // before that 429 arrives, surfacing as `cURL error 28 / 0 bytes`. Raising
        // the timeout to 15s lets the SDK actually receive the 429 instead of timing
        // out — mirroring what raw curl (--max-time 12) does.
        $nonSubscribeRequestTimeout = 15;

        $this->config = new PNConfiguration();
        $this->config->setSubscribeKey($subscribeKey);
        $this->config->setPublishKey($publishKey);
        $this->config->setUuid($uuidMock);
        $this->config->setNonSubscribeRequestTimeout($nonSubscribeRequestTimeout);

        $this->config_enc = new PNConfiguration();
        $this->config_enc->setSubscribeKey($subscribeKey);
        $this->config_enc->setPublishKey($publishKey);
        $this->config_enc->setCipherKey(static::CIPHER_KEY);
        $this->config_enc->setUuid($uuidMock);
        $this->config_enc->setNonSubscribeRequestTimeout($nonSubscribeRequestTimeout);

        $this->config_pam = new PNConfiguration();
        $this->config_pam->setSubscribeKey($subscribeKeyPam);
        $this->config_pam->setPublishKey($publishKeyPam);
        $this->config_pam->setSecretKey($secretKeyPam);
        $this->config_pam->setUuid($uuidMock);
        $this->config_pam->setNonSubscribeRequestTimeout($nonSubscribeRequestTimeout);

        $this->config_demo = new PNConfiguration();
        $this->config_demo->setSubscribeKey('demo');
        $this->config_demo->setPublishKey('demo');
        $this->config_demo->setUuid($uuidMock);
        $this->config_demo->setNonSubscribeRequestTimeout($nonSubscribeRequestTimeout);
        $this->config_demo->disableImmutableCheck();

        $this->pubnub = new PubNub($this->config);
        $this->pubnub_enc = new PubNub($this->config_enc);
        $this->pubnub_pam = new PubNub($this->config_pam);
        $this->pubnub_demo = new PubNub($this->config_demo);

        $this->pubnub->setLogger($logger);
        $this->pubnub_enc->setLogger($logger);
        $this->pubnub_pam->setLogger($logger);
        $this->pubnub_demo->setLogger($logger);

        $this->encodedSdkName = PubNubUtil::urlEncode($this->pubnub->getSdkFullName());
    }
}
