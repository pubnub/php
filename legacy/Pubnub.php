<?php

require_once('PubnubAES.php');
require_once('PubnubException.php');


/**
 * PubNub 3.5 Real-time Push Cloud API
 * @package Pubnub
 */
class Pubnub
{
    private $ORIGIN = 'pubsub.pubnub.com'; // Change this to your custom origin, or IUNDERSTAND.pubnub.com
    private $PUBLISH_KEY = 'demo';
    private $SUBSCRIBE_KEY = 'demo';
    private $SECRET_KEY = '';
    private $CIPHER_KEY = '';
    private $SSL = false;
    private $SESSION_UUID = '';
    private $PROXY = false;
    private $NEW_STYLE_RESPONSE = true;
    private $PEM_PATH = __DIR__;

    public $AES;
    private $PAM = null;

    /**
     * __construct
     *
     * Init the Pubnub Client API
     *
     * @param string $first_argument required key to send messages.
     * @param string $subscribe_key required key to receive messages.
     * @param bool|string $secret_key optional key to sign messages.
     * @param bool $cipher_key
     * @param boolean $ssl required for 2048 bit encrypted messages.
     * @param bool|string $origin optional setting for cloud origin.
     * @param bool $pem_path
     * @param bool $proxy
     */
    public function __construct(
        $first_argument = 'demo',
        $subscribe_key = 'demo',
        $secret_key = false,
        $cipher_key = false,
        $ssl = false,
        $origin = false,
        $pem_path = false,
        $uuid = false,
        $proxy = false
    ) {
        if (is_array($first_argument)) {
            $publish_key = isset($first_argument['publish_key']) ? $first_argument['publish_key'] : 'demo';
            $subscribe_key = isset($first_argument['subscribe_key']) ? $first_argument['subscribe_key'] : 'demo';
            $secret_key = isset($first_argument['secret_key']) ? $first_argument['secret_key'] : false;
            $cipher_key = isset($first_argument['cipher_key']) ? $first_argument['cipher_key'] : false;
            $ssl = isset($first_argument['ssl']) ? $first_argument['ssl'] : false;
            $origin = isset($first_argument['origin']) ? $first_argument['origin'] : false;
            $pem_path = isset($first_argument['pem_path']) ? $first_argument['pem_path'] : false;
            $uuid = isset($first_argument['uuid']) ? $first_argument['uuid'] : false;
            $proxy = isset($first_argument['proxy']) ? $first_argument['proxy'] : false;
        } else {
            $publish_key = $first_argument;
        }

        $this->SESSION_UUID = $uuid ? $uuid : self::uuid();
        $this->PUBLISH_KEY = $publish_key;
        $this->SUBSCRIBE_KEY = $subscribe_key;
        $this->SECRET_KEY = $secret_key;
        $this->SSL = $ssl;
        $this->PROXY = $proxy;
        $this->AES = new PubnubAES();

        if (!$this->AES->isBlank($cipher_key)) {
            $this->CIPHER_KEY = $cipher_key;
        }

        if ($pem_path != false) $this->PEM_PATH = $pem_path;

        if ($origin) $this->ORIGIN = $origin;

        if ($this->ORIGIN == "PHP.pubnub.com") {
            trigger_error("Before running in production, please contact support@pubnub.com for your custom origin.\nPlease set the origin from PHP.pubnub.com to IUNDERSTAND.pubnub.com to remove this warning.\n", E_USER_NOTICE);
        }

        if ($ssl) $this->ORIGIN = 'https://' . $this->ORIGIN;
        else      $this->ORIGIN = 'http://' . $this->ORIGIN;
    }

 
    function Pubnub()
    {
        $args = func_get_args();
        call_user_func(array($this, '__constructor'), $args);
    }

    /**
     * Publish
     *
     * Sends a message to a channel.
     *
     * @param string $channel
     * @param string $messageOrg
     * @throws PubnubException
     * @return array success information.
     */
    public function publish($channel, $messageOrg)
    {
        ## Fail if bad input.
        if (empty($channel) || empty($messageOrg)) {
            throw new PubnubException('Missing Channel or Message in publish()');
        }

        $message = $this->sendMessage($messageOrg);

        ## Sign Message
        $signature = "0";
        if ($this->SECRET_KEY) {
            ## Generate String to Sign
            $string_to_sign = implode('/', array(
                $this->PUBLISH_KEY,
                $this->SUBSCRIBE_KEY,
                $this->SECRET_KEY,
                $channel,
                $message
            ));
            $signature = md5($string_to_sign);
        }

        ## Send Message
        $publishResponse = $this->request(array(
            'publish',
            $this->PUBLISH_KEY,
            $this->SUBSCRIBE_KEY,
            $signature,
            $channel,
            '0',
            $message
        ));

        if ($publishResponse == null) {
            throw new PubnubException("Error during publish - response is null.");
        } else {
            return $publishResponse;
        }
    }

    /**
     * sendMessage
     *
     * Encoding message.
     *
     * @param string|array $messageOrg with message
     * @return string encoded message.
     */
    public function sendMessage($messageOrg)
    {
        if ($this->CIPHER_KEY != false) {
            $message = json_encode($this->AES->encrypt(json_encode($messageOrg), $this->CIPHER_KEY));
        } else {
            $message = json_encode($messageOrg);

        }
        return $message;
    }

    /**
     * hereNow
     *
     * Gets a list of uuids subscribed to the channel.
     *
     * @param $channel
     * @throws PubnubException
     * @return array of uuids and occupancies.
     */
    public function hereNow($channel)
    {
        if (empty($channel)) {
            throw new PubnubException('Missing Channel in hereNow()');
        }

        $response = $this->request(array(
            'v2',
            'presence',
            'sub_key',
            $this->SUBSCRIBE_KEY,
            'channel',
            $channel
        ));

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
     * Subscribe
     *
     * This is BLOCKING.
     * Listen for a message on a channel.
     *
     * @param string $channel for channel name
     * @param string $callback  for callback definition.
     * @param int $timeToken for current time token value.
     * @param bool $presence
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
                $returnArray = $this->NEW_STYLE_RESPONSE ? array($receivedMessages, $derivedChannel, $timeToken) : array($receivedMessages, $timeToken);

                # Call once for each message for each channel
                $exit_now = false;
                for ($i = 0; $i < sizeof($receivedMessages); $i++) {
                    $cbReturn = $callback(array("message" => $returnArray[0][$i], "channel" => $returnArray[1][$i], "timeToken" => $returnArray[2]));
                    if ($cbReturn == false) {
                        $exit_now = true;
                    }
                }
                if ($exit_now) {
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
     * @return array
     */
    public function decodeDecryptLoop($messageArray)
    {
        $receivedMessages = array();
        foreach ($messageArray as $message) {

            if ($this->CIPHER_KEY) {
                $decryptedMessage = $this->AES->decrypt($message, $this->CIPHER_KEY);
                $message = self::decode($decryptedMessage);
            }

            array_push($receivedMessages, $message);
        }

        return $receivedMessages;
    }

    /**
     * Presence
     *
     * This is BLOCKING.
     * Listen for a message on a channel.
     * @param string $channel
     * @param callback $callback
     * @param int $timeToken
     */
    public function presence($channel, $callback, $timeToken = 0)
    {
        ## Capture User Input
        $channel = $channel . "-pnpres";
        $this->subscribe($channel, $callback, $timeToken = 0, true);
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
        ## Get History
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
     * @param string $channel
     * @param int $count
     * @param bool $include_token
     * @param int $start
     * @param int $end
     * @param bool $reverse
     * @return array
     * @throws PubnubException
     */
    public function history($channel, $count = 100, $include_token = null, $start = null,
        $end = null, $reverse = false)
    {
        ## Capture User Input
        ## Fail if bad input.
        if (empty($channel)) {
            throw new PubnubException('Missing Channel in history()');
        }

        // input parameters pushed to array for iteration
        $args = array(
            'count' => $count,
            'start' => $start,
            'end' => $end,
            'include_token' => $include_token,
            'reverse' => $reverse
        );

        $urlParams = "";
        $urlParamsKeys = array('count', 'start', 'end');
        $urlBoolParamsKey = array('reverse', 'include_token');
        $urlParamsArray = array();

        foreach ($urlParamsKeys as $key) {
            if (!isset($args[$key])) continue;
            $urlParamsArray[] = sprintf('%s=%s', $key, $args[$key]);
        }

        foreach ($urlBoolParamsKey as $key) {
            if (!isset($args[$key])) continue;
            $urlParamsArray[] = sprintf('%s=%s', $key, (int)$args[$key] ? 'true' : 'false');
        }

        if (count($urlParamsArray)) {
            $urlParams = '?' . implode('&', $urlParamsArray);
        }

        $response = $this->request(array(
            'v2',
            'history',
            "sub-key",
            $this->SUBSCRIBE_KEY,
            "channel",
            $channel,
        ), array($urlParams));

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
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * Preprocessing request URL
     *
     * @param array $request of url directories and options $optArray for curl handle.
     * @param bool $urlParams
     * @param $optArray
     * @return Resource handle.
     */
    private function preprocRequest($request, $urlParams = false, $optArray)
    {

        $request = array_map('Pubnub::encode', $request);

        array_unshift($request, $this->ORIGIN);

        if (($request[1] === 'presence') || ($request[1] === 'subscribe')) {
            array_push($request, '?uuid=' . $this->SESSION_UUID);
        }

        $urlString = implode('/', $request);

        if ($urlParams) {
            $urlString .= $urlParams;
        }

        $ch = curl_init();

        curl_setopt_array($ch, $optArray);
        curl_setopt($ch, CURLOPT_URL, $urlString);

        return $ch;
    }

     /**
     * @param array $request of url directories.
     * @param array $urlParams
     * @return array|mixed|NULL|string from JSON response.
     * @throws PubnubException
     */
    private function request(array $request, $urlParams = array(false))
    {

        $optArray = array(CURLOPT_USERAGENT => "PHP",
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 310
        );

        if ($this->PROXY) {
            $optArray [CURLOPT_PROXY] = $this->PROXY;
        }

        if ($this->SSL) {
            $optArray [CURLOPT_SSL_VERIFYPEER] = true;
            $optArray [CURLOPT_SSL_VERIFYHOST] = 2;

            $pemPathAndFilename = $this->PEM_PATH . "/pubnub.com.pem";

            if (file_exists($pemPathAndFilename))
                $optArray [CURLOPT_CAINFO] = $pemPathAndFilename;
            else {
                trigger_error("Can't find PEM file. Please set pem_path in initializer.");
                exit;
            }

            $pubnubHeaders = array("V: 3.4", "Accept: */*"); // GZIP Support
            $optArray [CURLOPT_HTTPHEADER] = $pubnubHeaders;
        }

        if (!is_array($request[0])) {
            $ch = $this->preprocRequest($request, $urlParams[0], $optArray);

            $output = curl_exec($ch);
            $curlError = curl_errno($ch);
            $curlResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $JSONDecodedResponse = self::decode($output);

            curl_close($ch);

            if ($JSONDecodedResponse != null)
                return $JSONDecodedResponse;
            elseif ($curlError == 28)
                throw new PubnubException ("_PUBNUB_TIMEOUT");
            elseif ($curlResponseCode == 400 || $curlResponseCode == 404)
                throw new PubnubException ("_PUBNUB_MESSAGE_TOO_LARGE");

        } else {
            $mh = curl_multi_init();
            $chArray = array();
            $result = array();

            curl_multi_setopt($mh, CURLMOPT_PIPELINING, 1); // optimal TCP packet usage.
            curl_multi_setopt($mh, CURLMOPT_MAXCONNECTS, 100); // concurrent sockets pipes.

            $chIndex = 0;

            foreach ($request as $i => $r) {
                array_push($chArray, $this->preprocRequest($r, $urlParams[$i], $optArray));
                curl_multi_add_handle($mh, $chArray[$chIndex]);
                $chIndex++;
            }

            $stillRunning = 0;

            do {
                $execReturnValue = curl_multi_exec($mh, $stillRunning);
                curl_multi_select($mh);
            } while ($stillRunning > 0);

            foreach ($chArray as $i => $c) {
                $curlError = curl_error($c);
                if ($curlError == "") {
                    $result[$i] = curl_multi_getcontent($c);
                } else {
                    throw new PubnubException ("Curl error on handle $i: $curlError\n");
                }

                curl_multi_remove_handle($mh, $c);
                curl_close($c);
            }

            curl_multi_close($mh);

            if ($execReturnValue != CURLM_OK) {
                return curl_multi_strerror($execReturnValue);
            } else return $result;
        }
    }

    /**
     * handleError
     *
     * for internal error handling
     *
     * @param $error
     */
    public function handleError($error)
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

    /**
     * Encode
     *
     * @param string $part of url directories.
     * @return string encoded string.
     */
    private static function encode($part)
    {
 
        $pieces = array_map('Pubnub::encodeChar', str_split($part));

        return implode('', $pieces);
    }

    /**
     * Encode Char
     *
     * @param string $char val.
     * @return string encoded char.
     */
    private static function encodeChar($char)
    {
        if (strpos(' ~`!@#$%^&*()+=[]\\{}|;\':",./<>?', $char) === false)
            return $char;
        else
            return rawurlencode($char);
    }

    /**
     * @param string $val
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     */
    private static function decode($val, $assoc = true, $depth = 512, $options = 0)
    {
 
        return json_decode($val, $assoc);


    }
}
