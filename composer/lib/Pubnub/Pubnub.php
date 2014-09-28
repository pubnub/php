<?php
 
namespace Pubnub;

use Exception;
use Pubnub\Clients\DefaultClient;
use Pubnub\Clients\PipelinedClient;


/**
 * PubNub 3.6 Real-time Push Cloud API
 *
 * @package Pubnub
 */
class Pubnub
{
    const PNSDK = 'Pubnub-PHP%2F3.6.1';

    private $ORIGIN = 'pubsub.pubnub.com'; // Change this to your custom origin, or IUNDERSTAND.pubnub.com
    private $PUBLISH_KEY;
    private $SUBSCRIBE_KEY;
    private $SECRET_KEY = '';
    private $CIPHER_KEY = '';
    private $AUTH_KEY = '';
    private $SSL = false;
    private $SESSION_UUID = '';
    private $PROXY = false;
    private $NEW_STYLE_RESPONSE = true;

    private $pipelinedFlag = false;
    private $defaultClient;
    private $pipelinedClient;

    public $AES;
    private $PAM = null;

    /**
     * Pubnub Client API constructor
     *
     * You can create client instance using named or positional arguments
     *
     * @param string|array $first_argument publish_key to send messages or associative config array.
     * @param string $subscribe_key required key to receive messages.
     * @param bool|string $secret_key optional key to sign messages.
     * @param bool $cipher_key
     * @param bool $ssl required for 2048 bit encrypted messages.
     * @param bool|string $origin optional setting for cloud origin.
     * @param bool $pem_path
     * @param bool $uuid
     * @param bool $proxy
     * @param bool $auth_key
     *
     * @throws PubnubException
     */
    public function __construct(
        $first_argument = '',
        $subscribe_key = '',
        $secret_key = false,
        $cipher_key = false,
        $ssl = false,
        $origin = false,
        $pem_path = false,
        $uuid = false,
        $proxy = false,
        $auth_key = false
    ) {

        if (is_array($first_argument)) {
            $publish_key = isset($first_argument['publish_key']) ? $first_argument['publish_key'] : '';
            $subscribe_key = isset($first_argument['subscribe_key']) ? $first_argument['subscribe_key'] : '';
            $secret_key = isset($first_argument['secret_key']) ? $first_argument['secret_key'] : false;
            $cipher_key = isset($first_argument['cipher_key']) ? $first_argument['cipher_key'] : false;
            $ssl = isset($first_argument['ssl']) ? $first_argument['ssl'] : false;
            $origin = isset($first_argument['origin']) ? $first_argument['origin'] : false;
            $pem_path = isset($first_argument['pem_path']) ? $first_argument['pem_path'] : false;
            $uuid = isset($first_argument['uuid']) ? $first_argument['uuid'] : false;
            $proxy = isset($first_argument['proxy']) ? $first_argument['proxy'] : false;
            $auth_key = isset($first_argument['auth_key']) ? $first_argument['auth_key'] : false;
        } else {
            $publish_key = $first_argument;
        }

        if (empty($publish_key)) {
            throw new PubnubException('Missing required $publish_key param');
        }

        if (empty($subscribe_key)) {
            throw new PubnubException('Missing required $subscribe_key param');
        }

        $this->SESSION_UUID = $uuid ? $uuid : self::uuid();
        $this->PUBLISH_KEY = $publish_key;
        $this->SUBSCRIBE_KEY = $subscribe_key;
        $this->SECRET_KEY = $secret_key;
        $this->SSL = $ssl;
        $this->PROXY = $proxy;
        $this->AES = new PubnubAES();
        $this->AUTH_KEY = $auth_key;

        $this->defaultClient = new DefaultClient($origin, $ssl, $proxy, $pem_path);
        $this->pipelinedClient = new PipelinedClient($origin, $ssl, $proxy, $pem_path);

        if (!$this->AES->isBlank($cipher_key)) {
            $this->CIPHER_KEY = $cipher_key;
        }
    }



    /**
     * Publish
     *
     * Sends a message to a channel.
     *
     * @param string $channel
     * @param string $messageOrg
     * @param boolean $storeInHistory
     *
     * @throws PubnubException
     *
     * @return array success information.
     */
    public function publish($channel, $messageOrg, $storeInHistory = true)
    {
        if (empty($channel) || empty($messageOrg)) {
            throw new PubnubException('Missing Channel or Message in publish()');
        }

        if (empty($this->PUBLISH_KEY)) {
            throw new PubnubException('Missing Publish Key in publish()');
        }

        if ($this->CIPHER_KEY != false) {
            $message = JSON::encode($this->AES->encrypt(json_encode($messageOrg), $this->CIPHER_KEY));
        } else {
            $message = JSON::encode($messageOrg);
        }

        $query = array();

        if ($storeInHistory == false) {
            $query["store"] = "0";
        }

        $signature = "0";

        if ($this->SECRET_KEY) {
            $string_to_sign = implode('/', array(
                $this->PUBLISH_KEY,
                $this->SUBSCRIBE_KEY,
                $this->SECRET_KEY,
                $channel,
                $message
            ));

            $signature = md5($string_to_sign);
        }

        return $this->request(array(
            'publish',
            $this->PUBLISH_KEY,
            $this->SUBSCRIBE_KEY,
            $signature,
            $channel,
            '0',
            $message
        ), $query);
    }

    /**
     * Gets a list of uuids subscribed to the channel.
     *
     * @param string $channel
     * @param bool $disable_uuids
     * @param bool $state
     *
     * @throws PubnubException
     *
     * @return array of uuids and occupancies.
     */
    public function hereNow($channel = null, $disable_uuids = false, $state = false)
    {
        if (isset($channel) && empty($channel)) {
            throw new PubnubException('Missing Channel in hereNow()');
        }

        $requestArray = array(
            'v2',
            'presence',
            'sub_key',
            $this->SUBSCRIBE_KEY
        );

        $query = array();

        if ($channel !== null) {
            array_push($requestArray, 'channel', $channel);
        }

        if ($disable_uuids) {
            $query['disable_uuids'] = 1;
        }

        if ($state) {
            $query['state'] = 1;
        }

        $response = $this->request($requestArray, $query);

        //TODO: <timeout> and <message too large> check
        if (!is_array($response)) {
            $response = array(
                'uuids' => array(),
                'occupancy' => 0,
            );
        }

        return $response;
    }

    /**
     * Set metadata for user with given uuid
     *
     * @param string $channel
     * @param array|null $state
     * @param string|null $uuid
     *
     * @throws PubnubException
     *
     * @return mixed|null
     */
    public function setState($channel, $state, $uuid = null)
    {
        if (empty($channel)) {
            throw new PubnubException('Missing Channel in setState()');
        }

        $uuid = $uuid ? $uuid : $this->SESSION_UUID;
        $state = JSON::encode($state);

        return $this->request(array(
            'v2',
            'presence',
            'sub_key',
            $this->SUBSCRIBE_KEY,
            'channel',
            $channel,
            'uuid',
            $uuid,
            'data'
        ), array(
            'state' => $state
        ));
    }

    /**
     * Returns metadata for user with given uuid
     *
     * @param string $channel
     * @param string $uuid
     *
     * @throws PubnubException
     *
     * @return array|null
     */
    public function getState($channel, $uuid)
    {
        if (empty($channel)) {
            throw new PubnubException('Missing Channel in getState()');
        }

        if (empty($uuid)) {
            throw new PubnubException('Missing UUID in getState()');
        }

        return $this->request(array(
            'v2',
            'presence',
            'sub_key',
            $this->SUBSCRIBE_KEY,
            'channel',
            $channel,
            'uuid',
            $uuid
        ));
    }

    /**
     * Subscribe
     *
     * This is BLOCKING.
     * Listen for a message on a channel.
     *
     * @param string $channel for channel name
     * @param string $callback  for callback definition.
     * @param int $timeToken for current time token value.
     * @param bool $presence
     *
     * @throws PubnubException
     */
    public function subscribe($channel, $callback, $timeToken = 0, $presence = false)
    {
        if (empty($channel)) {
            throw new PubnubException("Missing Channel in subscribe()");
        }

        if (empty($callback)) {
            throw new PubnubException("Missing Callback in subscribe()");
        }

        if (is_array($channel)) {
            $channel = join(',', $channel);
        }

        if ($presence == true) {
            $mode = "presence";
        } else {
            $mode = "default";
        }

        while (1) {
            try {
                ## Wait for Message
                $response = $this->request(array(
                    'subscribe',
                    $this->SUBSCRIBE_KEY,
                    $channel,
                    '0',
                    $timeToken
                ));

                $messages = $response[0];
                $timeToken = $response[1];

                if ($response == "_PUBNUB_TIMEOUT") {
                    continue;
                } elseif ($response == "_PUBNUB_MESSAGE_TOO_LARGE") {
                    $timeToken = $this->throwAndResetTimeToken($callback, "Message Too Large");
                    continue;
                } elseif ($response == null || $timeToken == null) {
                    $timeToken = $this->throwAndResetTimeToken($callback, "Bad server response.");
                    continue;
                }

                // determine the channel

                if ((count($response) == 3)) {
                    $derivedChannel = explode(",", $response[2]);
                } else {
                    $channelArray = array();

                    for ($a = 0; $a < sizeof($messages); $a++) {
                        array_push($channelArray, $channel);
                    }

                    $derivedChannel = $channelArray;
                }

                if (!count($messages)) {
                    continue;
                }

                $receivedMessages = $this->decodeAndDecrypt($messages, $mode);

                $returnArray = $this->NEW_STYLE_RESPONSE
                    ? array($receivedMessages, $derivedChannel, $timeToken)
                    : array($receivedMessages, $timeToken);

                # Call once for each message for each channel
                $exit_now = false;

                for ($i = 0; $i < sizeof($receivedMessages); $i++) {
                    $cbReturn = $callback(array("message" => $returnArray[0][$i], "channel" => $returnArray[1][$i], "timeToken" => $returnArray[2]));
                    if ($cbReturn == false) {
                        $exit_now = true;
                    }
                }

                if ($exit_now) {
                    $channels = explode(',', $channel);

                    foreach ($channels as $ch) {
                        $this->leave($ch);
                    }

                    return;
                }

            } catch (Exception $error) {
                $this->handleError($error);
                $timeToken = $this->throwAndResetTimeToken($callback, "Unknown error.");
                continue;
            }
        }
    }


    /**
     * decodeAndDecrypt
     *
     * @param string $messages
     * @param string $mode
     * @internal
     *
     * @return array
     */
    public function decodeAndDecrypt($messages, $mode = "default")
    {
        $receivedMessages = array();

        if ($mode == "presence") {
            return $messages;
        } elseif ($mode == "default" && is_array($messages)) {
            $messageArray = $messages;
            $receivedMessages = $this->decodeDecryptLoop($messageArray);
        } elseif ($mode == "detailedHistory" && is_array($messages)) {
            $decodedMessages = $this->decodeDecryptLoop($messages);
            $receivedMessages = array($decodedMessages[0], $messages[1], $messages[2]);
        }

        return $receivedMessages;
    }

    /**
     * @param $messageArray
     * @internal
     *
     * @return array
     */
    public function decodeDecryptLoop($messageArray)
    {
        $receivedMessages = array();
        foreach ($messageArray as $message) {

            if ($this->CIPHER_KEY) {
                $decryptedMessage = $this->AES->decrypt($message, $this->CIPHER_KEY);
                $message = JSON::decode($decryptedMessage);
            }

            array_push($receivedMessages, $message);
        }

        return $receivedMessages;
    }

    /**
     * Presence
     *
     * This is BLOCKING.
     * Listen for a message on a presence channel.
     *
     * @param string $channel
     * @param callback $callback
     * @param int $timeToken
     */
    public function presence($channel, $callback, $timeToken = 0)
    {
        ## Capture User Input
        $channel = $channel . "-pnpres";
        $this->subscribe($channel, $callback, $timeToken, true);
    }

    /**
     * Time
     *
     * Timestamp from PubNub Cloud.
     *
     * @return int timestamp.
     */
    public function time()
    {
        $response = $this->request(array(
            'time',
            '0'
        ));

        $result = (isset($response[0])) ? $response[0] : 0;
        if (is_string($result)) {
            $result = intval(substr($result, 0, 10));
        }

        return $result;
    }

    /**
     * Updates current UUID
     *
     * @throws PubnubException
     *
     * @param string $uuid
     */
    public function setUUID($uuid)
    {
        if (empty($uuid)) {
            throw new PubnubException('Empty UUID in setUUID()');
        }

        $this->SESSION_UUID = $uuid;
    }

    /**
     * Returns current UUID
     *
     * @return string UUID
     */
    public function getUUID()
    {
        return $this->SESSION_UUID;
    }

    /**
    * Returns channel list for defined UUID
     *
     * @param string $uuid
     *
     * @throws PubnubException
     *
     * @return array
     */
    public function whereNow($uuid = "")
    {
        if (empty($this->SUBSCRIBE_KEY)) {
            throw new PubnubException('Missing subscribe key in whereNow()');
        }

        $response = $this->request(array(
            'v2',
            'presence',
            'sub_key',
            $this->SUBSCRIBE_KEY,
            'uuid',
            $uuid
        ), array(
            'uuid' => empty($uuid) ? $this->SESSION_UUID : $uuid
        ));

        return $response;
    }

    /**
     * Retrieve the messages on a channel
     *
     * @param string $channel
     * @param int $count message limit
     * @param bool $include_token for each message
     * @param int $start time token
     * @param int $end time token
     * @param bool $reverse
     *
     * @throws PubnubException
     *
     * @return array
     */
    public function history($channel, $count = 100, $include_token = null, $start = null,
        $end = null, $reverse = false)
    {
        if (empty($channel)) {
            throw new PubnubException('Missing Channel in history()');
        }

        $urlIntParamsKeys = array('count', 'start', 'end');
        $urlBoolParamsKey = array('reverse', 'include_token');
        $query = array();

        $args = array(
            'count' => $count,
            'start' => $start,
            'end' => $end,
            'include_token' => $include_token,
            'reverse' => $reverse
        );

        foreach ($urlIntParamsKeys as $key) {
            if (empty($args[$key])) continue;
            $query[$key] = (int)$args[$key];
        }

        foreach ($urlBoolParamsKey as $key) {
            if (empty($args[$key])) continue;
            $query[$key] = (int)$args[$key] ? 'true' : 'false';
        }

        $response = $this->request(array(
            'v2',
            'history',
            "sub-key",
            $this->SUBSCRIBE_KEY,
            "channel",
            $channel,
        ), $query);

        $receivedMessages = $this->decodeAndDecrypt($response, "detailedHistory");

        //TODO: <timeout> and <message too large> check
        if (!is_array($receivedMessages)) {
            $receivedMessages = array();
        }

        $result = array(
            'messages' => isset($receivedMessages[0]) ? $receivedMessages[0] : array(),
            'date_from' => isset($receivedMessages[1]) ? $receivedMessages[1] : 0,
            'date_to' => isset($receivedMessages[2]) ? $receivedMessages[2] : 0,
        );

        return $result;
    }

    /**
     * @deprecated 3.7.0
     * @see Pubnub->history() Use history() instead
     */
    function detailedHistory()
    {
        trigger_error('detailedHistory() methods is deprecated. Use history() instead.', E_USER_DEPRECATED);

        $args = func_get_args();

        return call_user_func_array(array($this, 'history'), $args);
    }

    /**
     * UUID
     *
     * UUID generator
     *
     * @return string UUID
     */
    public static function uuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        } else {
            return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535),
                mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535),
                mt_rand(0, 65535));
        }

    }

    /**
     * Establish subscribe and/or write permissions
     *
     * @param bool $read
     * @param bool $write
     * @param string|null $channel
     * @param string|null $auth_key
     * @param int|null $ttl
     *
     * @return array
     */
    public function grant($read, $write, $channel = null, $auth_key = null, $ttl = null) {

        $request_params = $this->pam()->grant($read, $write, $channel, $auth_key, $ttl, $this->SESSION_UUID);

        return $this->request($request_params['url'], $request_params['search'], false);
    }

    /**
     * Reveal existing permissions
     *
     * @param string|null $channel
     * @param string|null $auth_key
     *
     * @return array
     */
    public function audit($channel = null, $auth_key = null) {

        $request_params = $this->pam()->audit($channel, $auth_key, $this->SESSION_UUID);

        return $this->request($request_params['url'], $request_params['search'], false);
    }

    /**
     * Revoke all permissions
     *
     * @param string|null $channel
     * @param string|null $auth_key
     *
     * @return array
     */
    public function revoke($channel = null, $auth_key = null) {

        $request_params = $this->pam()->revoke($channel, $auth_key, $this->SESSION_UUID);

        return $this->request($request_params['url'], $request_params['search'], false);
    }

    /**
     * Pipelines multiple requests into a single connection.
     * For PHP <= 5.3 use pipelineStart() and pipelineEnd() functions instead.
     *
     * @param Callback $callback
     */
    public function pipeline($callback)
    {
        $this->pipelinedFlag = true;
        $callback($this);
        $this->pipelinedClient->execute();
        $this->pipelinedFlag = false;
    }

    /**
     * Start collecting messages to send them in pipelined mode.
     */
    public function pipelineStart()
    {
        $this->pipelinedFlag = true;
    }

    /**
     * End collecting messages and send them in pipelined mode.
     */
    public function pipelineEnd()
    {
        $this->pipelinedClient->execute();
        $this->pipelinedFlag = false;
    }

    /**
     * Set auth_key after instantiation
     *
     * @param $auth_key
     */
    public function setAuthKey($auth_key)
    {
        $this->AUTH_KEY = $auth_key;
    }

    private function leave($channel)
    {
        $this->request(array(
            'v2',
            'presence',
            'sub_key',
            $this->SUBSCRIBE_KEY,
            'channel',
            $channel,
            'leave'
        ));
    }

    /**
     * Performs request depending on pipelined/non-pipelined mode
     *
     * In pipelined mode null is returned for every query
     * In non-pipelined mode response array is returned
     *
     * @param array $path
     * @param array $query
     * @param bool $useDefaultQueryArray
     *
     * @throws PubnubException
     *
     * @return array|null
     */
    private function request(array $path, array $query = array(), $useDefaultQueryArray = true)
    {
        if ($useDefaultQueryArray) {
            $query = array_merge($query, $this->defaultQueryArray());
        }

        if ($this->pipelinedFlag === true) {
            $this->pipelinedClient->add($path, $query);

            return null;
        } else {
            $result = $this->defaultClient->add($path, $query);

            if ($result === null) {
                throw new PubnubException('Error while performing request. Method name:' . $path[0]);
            }

            return $result;
        }
    }

    /**
     * Prepare default query
     *
     * @return array
     */
    private function defaultQueryArray()
    {
        $query = array();

        $query['uuid'] = $this->SESSION_UUID;
        $query['pnsdk'] = self::PNSDK;

        if (!empty($this->AUTH_KEY)) {
            $query['auth'] = $this->AUTH_KEY;
        }

        return $query;
    }

    /**
     * Return PubnubPAM instance
     *
     * @return PubnubPAM
     */
    private function pam()
    {
        if ($this->PAM === null) {
            $this->PAM = new PubnubPAM($this->PUBLISH_KEY, $this->SUBSCRIBE_KEY, $this->SECRET_KEY, self::PNSDK);
        }

        return $this->PAM;
    }

    /**
     * handleError
     *
     * for internal error handling
     *
     * @param \Exception $error
     */
    private function handleError($error)
    {
        $errorMsg = 'Error on line ' . $error->getLine() . ' in ' . $error->getFile() . $error->getMessage();
        trigger_error($errorMsg, E_COMPILE_WARNING);
        sleep(1);
    }

    /**
     * @param $callback
     * @param $errorMessage
     * @return string
     */
    private function throwAndResetTimeToken($callback, $errorMessage)
    {
        $callback(array(0, $errorMessage));
        $timeToken = "0";

        return $timeToken;
    }
}
