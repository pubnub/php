<?php

namespace PubNub\Builders;


use PubNub\PubNubError;

class PubNubErrorBuilder
{
    // Error Codes

    /**
     * Timeout Error .
     */
    const PNERR_TIMEOUT = 100;

    /**
     *
     */
    const PNERR_PUBNUB_ERROR = 101;

    /**
     * Connect Exception . Network Unreachable.
     */
    const PNERR_CONNECT_EXCEPTION = 102;

    /**
     * Please check network connectivity. Please contact support with error
     * details if issue persists.
     */
    const PNERR_HTTP_ERROR = 103;

    /**
     * Client Timeout .
     */
    const PNERR_CLIENT_TIMEOUT = 104;

    /**
     * An ULS singature error occurred . Please contact support with error
     * details.
     */
    const PNERR_ULSSIGN_ERROR = 105;

    /**
     * Please verify if network is reachable
     */
    const PNERR_NETWORK_ERROR = 106;

    /**
     * PubNub Exception .
     */
    const PNERR_PUBNUB_EXCEPTION = 108;

    /**
     * Disconnect .
     */
    const PNERR_DISCONNECT = 109;

    /**
     * Disconnect and Resubscribe Received .
     */
    const PNERR_DISCONN_AND_RESUB = 110;

    /**
     * Gateway Timeout
     */
    const PNERR_GATEWAY_TIMEOUT = 111;

    /**
     * PubNub server returned HTTP 403 forbidden status code. Happens when wrong
     * authentication key is used .
     */
    const PNERR_FORBIDDEN = 112;
    /**
     * PubNub server returned HTTP 401 unauthorized status code Happens when
     * authentication key is missing .
     */
    const PNERR_UNAUTHORIZED = 113;

    /**
     * Secret key not configured
     */
    const PNERR_SECRET_KEY_MISSING = 114;

    // internal error codes

    /**
     * Error while encrypting message to be published to PubNub Cloud . Please
     * contact support with error details.
     */
    const PNERR_ENCRYPTION_ERROR = 115;

    /**
     * Decryption Error . Please contact support with error details.
     */
    const PNERR_DECRYPTION_ERROR = 116;

    /**
     * Invalid Json . Please contact support with error details.
     */
    const PNERR_INVALID_JSON = 117;

    /**
     * Unable to open input stream . Please contact support with error details.
     */
    const PNERR_GETINPUTSTREAM = 118;

    /**
     * Malformed URL . Please contact support with error details .
     */
    const PNERR_MALFORMED_URL = 119;

    /**
     * Error in opening URL . Please contact support with error details.
     */
    const PNERR_URL_OPEN = 120;

    /**
     * JSON Error while processing API response. Please contact support with
     * error details.
     */
    const PNERR_JSON_ERROR = 121;

    /**
     * Protocol Exception . Please contact support with error details.
     */
    const PNERR_PROTOCOL_EXCEPTION = 122;

    /**
     * Unable to read input stream . Please contact support with error details.
     */
    const PNERR_READINPUT = 123;

    /**
     * Bad gateway . Please contact support with error details.
     */
    const PNERR_BAD_GATEWAY = 124;

    /**
     * PubNub server returned HTTP 502 internal server error status code. Please
     * contact support with error details.
     */
    const PNERR_INTERNAL_ERROR = 125;

    /**
     * Parsing Error .
     */
    const PNERR_PARSING_ERROR = 126;

    /**
     * Bad Request . Please contact support with error details.
     */
    const PNERR_BAD_REQUEST = 127;

    const PNERR_HTTP_RC_ERROR = 128;
    /**
     * PubNub server or intermediate server returned HTTP 404 unauthorized
     * status code
     *
     */
    const PNERR_NOT_FOUND = 129;

    /**
     * Subscribe Timeout .
     */
    const PNERR_HTTP_SUBSCRIBE_TIMEOUT = 130;

    /**
     * Invalid arguments provided to API
     *
     */
    const PNERR_INVALID_ARGUMENTS = 131;

    /**
     * Channel missing
     *
     */
    const PNERR_CHANNEL_MISSING = 132;

    /**
     * PubNub connection not set on sender
     *
     */
    const PNERR_CONNECTION_NOT_SET = 133;

    /**
     * Error while parsing group name
     */
    const PNERR_CHANNEL_GROUP_PARSING_ERROR = 134;

    /**
     * Crypto Error
     */
    const PNERR_CRYPTO_ERROR = 135;

    /**
     * Group missing
     *
     */
    const PNERR_GROUP_MISSING = 136;

    /**
     * Auth Keys missing
     *
     */
    const PNERR_AUTH_KEYS_MISSING = 137;

    /**
     * Subscribe Key missing
     *
     */
    const PNERR_SUBSCRIBE_KEY_MISSING = 138;

    /**
     * Publish Key missing
     *
     */
    const PNERR_PUBLISH_KEY_MISSING = 139;

    /**
     * State missing
     *
     */
    const PNERR_STATE_MISSING = 140;

    /**
     * Channel and Group missing
     *
     */
    const PNERR_CHANNEL_AND_GROUP_MISSING = 141;

    /**
     * Message missing
     *
     */
    const PNERR_MESSAGE_MISSING = 142;

    /**
     * Push TYpe missing
     *
     */
    const PNERR_PUSH_TYPE_MISSING = 143;

    /**
     * Device ID missing
     *
     */
    const PNERR_DEVICE_ID_MISSING = 144;

    protected static $instance;

    /** @var PubNubError PNERROBJ_MESSAGE_MISSING */
    public $PNERROBJ_MESSAGE_MISSING;

    /** @var PubNubError UNEXPECTED_REQUESTS_EXCEPTION*/
    public $UNEXPECTED_REQUESTS_EXCEPTION ;

    public function __construct()
    {
    }

    public static function predefined()
    {
        if (static::$instance == null) {
            static::$instance = new static();
            static::initPredefinedErrors();
        }

        return static::$instance;
    }

    private static function initPredefinedErrors()
    {
        static::$instance->PNERROBJ_MESSAGE_MISSING = (new PubNubError())
            ->setErrorCode(static::PNERR_MESSAGE_MISSING)
            ->setErrorString("Message Missing.");

        static::$instance->PNERROBJ_CHANNEL_MISSING = (new PubNubError())
            ->setErrorCode(static::PNERR_CHANNEL_MISSING)
            ->setErrorString("Channel Missing.");

        static::$instance->PNERROBJ_SUBSCRIBE_KEY_MISSING = (new PubNubError())
            ->setErrorCode(static::PNERR_SUBSCRIBE_KEY_MISSING)
            ->setErrorString("ULS configuration failed. Subscribe Key not configured.");

        static::$instance->PNERROBJ_PUBLISH_KEY_MISSING = (new PubNubError())
            ->setErrorCode(static::PNERR_PUBLISH_KEY_MISSING)
            ->setErrorString("ULS configuration failed. Publish Key not configured.");

        static::$instance->PNERROBJ_SECRET_KEY_MISSING = (new PubNubError())
            ->setErrorCode(static::PNERR_SECRET_KEY_MISSING)
            ->setErrorString("ULS configuration failed. Secret Key not configured.");

        static::$instance->UNEXPECTED_REQUESTS_EXCEPTION = (new PubNubError())
            ->setErrorCode(0)
            ->setErrorString("Unexpected exception while invoking request.");
    }
}
