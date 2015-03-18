<?php
 
namespace Pubnub;

use Exception;
use Pubnub\Clients\DefaultClient;
use Pubnub\Clients\PipelinedClient;


/**
 * PubNub 3.7.6 Real-time Push Cloud API
 *
 * @package Pubnub
 */
class Pubnub
{
    const PNSDK = 'Pubnub-PHP/3.7.6';

    private $PUBLISH_KEY;
    private $SUBSCRIBE_KEY;
    private $SECRET_KEY = '';
    private $CIPHER_KEY = '';
    private $AUTH_KEY = '';
    private $SSL = false;
    private $SESSION_UUID = '';
    private $PROXY = false;
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
     * @param bool $verify_peer
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
        $auth_key = false,
        $verify_peer = true
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
            $verify_peer = isset($first_argument['verify_peer']) ? $first_argument['verify_peer'] : $verify_peer;
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

        $this->defaultClient = new DefaultClient($origin, $ssl, $proxy, $pem_path, $verify_peer);
        $this->pipelinedClient = new PipelinedClient($origin, $ssl, $proxy, $pem_path, $verify_peer);

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
            $message = PubnubUtil::json_encode($this->AES->encrypt(json_encode($messageOrg), $this->CIPHER_KEY));
        } else {
            $message = PubnubUtil::json_encode($messageOrg);
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
            PubnubUtil::url_encode($signature),
            PubnubUtil::url_encode($channel),
            '0',
            PubnubUtil::url_encode($message)
        ), $query);
    }

    /**
     * Gets a list of uuids subscribed to the channel.
     *
     * @param string $channel
     * @param bool $disable_uuids
     * @param bool $state
     * @param string $channelGroup comma delimited list
     *
     * @throws PubnubException
     *
     * @return array of uuids and occupancies.
     */
    public function hereNow($channel = null, $disable_uuids = false, $state = false, $channelGroup = null)
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
            array_push($requestArray, 'channel', PubnubUtil::url_encode($channel));
        } else if ($channel === null && $channelGroup !== null) {
            array_push($requestArray, 'channel', ",");
        }

        if ($channelGroup !== null) {
            $query['channel-group'] = PubnubUtil::url_encode($channelGroup);
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
     * Gets a list of uuids subscribed to the channel groups.
     *
     * @param string $channelGroup comma delimited list
     * @param bool $disable_uuids
     * @param bool $state
     *
     * @throws PubnubException
     *
     * @return array of uuids and occupancies.
     */
    public function channelGroupHereNow($channelGroup, $disable_uuids = false, $state = false)
    {
        return $this->hereNow(null, $disable_uuids, $state, $channelGroup);
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
        $state = PubnubUtil::json_encode($state);

        return $this->request(array(
            'v2',
            'presence',
            'sub_key',
            $this->SUBSCRIBE_KEY,
            'channel',
            PubnubUtil::url_encode($channel),
            'uuid',
            PubnubUtil::url_encode($uuid),
            'data'
        ), array(
            'state' => $state
        ));
    }

    /**
     * Set metadata for all channels in the group
     *
     * @param string $group
     * @param array|null $state
     * @param string|null $uuid
     *
     * @throws PubnubException
     *
     * @return mixed|null
     */
    public function setChannelGroupState($group, $state, $uuid = null) {
        if (empty($group)) {
            throw new PubnubException('Missing Group in setChannelGroupState()');
        }

        $uuid = $uuid ? $uuid : $this->SESSION_UUID;
        $state = PubnubUtil::json_encode($state);

        return $this->request(array(
            'v2',
            'presence',
            'sub_key',
            $this->SUBSCRIBE_KEY,
            'channel',
            '.',
            'uuid',
            PubnubUtil::url_encode($uuid),
            'data'
        ), array(
            'state' => $state,
            'channel-group' => PubnubUtil::url_encode($group)
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
            PubnubUtil::url_encode($channel),
            'uuid',
            PubnubUtil::url_encode($uuid)
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

        $this->_subscribe($channel, null, $callback, $timeToken, $presence);
    }

    public function channelGroupSubscribe($group, $callback, $timetoken = 0)
    {
        if (empty($group)) {
            throw new PubnubException("Missing Group in channelGroupSubscribe()");
        }

        $this->_subscribe(null, $group, $callback, $timetoken);
    }

    protected function _subscribe($channel, $channelGroup, $callback, $timeToken = 0, $presence = false)
    {
        if (empty($callback)) {
            throw new PubnubException("Missing Callback in subscribe()");
        }

        $query = array();

        if (is_array($channelGroup)) {
            $channelGroup = join(',', $channelGroup);
        }

        if (is_array($channel)) {
            $channel = join(',', $channel);
        }

        if ($channel === null && $channelGroup !== null) {
            $channel = ',';
        } else {
            $channel = PubnubUtil::url_encode($channel);
        }

        if ($channelGroup !== null) {
            $query['channel-group'] = PubnubUtil::url_encode($channelGroup);
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
                ), $query, true, true);

                $messages = $response[0];
                $timeToken = $response[1];
                $derivedGroup = null;

                if ($response == null || $timeToken == null) {
                    $timeToken = $this->throwAndResetTimeToken($callback, "Bad server response.");
                    continue;
                }

                if ((count($response) == 4)) {
                    // Response has multiple channels or/and groups
                    $derivedChannel = explode(",", $response[3]);
                    $derivedGroup = explode(",", $response[2]);
                } else if ((count($response) == 3)) {
                    // Response has multiple channels
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

                $returnArray = array($receivedMessages, $derivedChannel, $timeToken);

                # Call once for each message for each channel
                $exit_now = false;

                for ($i = 0; $i < sizeof($receivedMessages); $i++) {
                    $resultArray = array(
                        "message" => $returnArray[0][$i],
                        "channel" => $returnArray[1][$i],
                        "timeToken" => $returnArray[2]
                    );

                    if (isset($derivedGroup)) {
                      $resultArray["group"] = $derivedGroup[$i];
                    }

                    $cbReturn = $callback($resultArray);

                    if ($cbReturn == false) {
                        $exit_now = true;
                    }
                }

                # Explicitly invoke leave event
                if ($exit_now) {
                    $channels = explode(',', $channel);

                    foreach ($channels as $ch) {
                        $this->leave($ch);
                    }

                    return;
                }

            } catch (PubnubException $error) {
                continue;
            } catch (\Exception $error) {
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
                $message = PubnubUtil::json_decode($decryptedMessage);
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
            PubnubUtil::url_encode($uuid)
        ), array(
            'uuid' => PubnubUtil::url_encode(empty($uuid) ? $this->SESSION_UUID : $uuid)
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
            PubnubUtil::url_encode($channel),
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
     * Get list of group's channels
     *
     * @param string $group name
     * @return array
     */
    public function channelGroupListChannels($group)
    {
        $channelGroup = new ChannelGroup($group);

        $path = array(
            "v1",
            "channel-registration",
            "sub-key",
            $this->SUBSCRIBE_KEY
        );

        if (!empty($channelGroup->namespace)) {
            array_push($path, "namespace", $channelGroup->namespace);
        }

        array_push($path, "channel-group", $channelGroup->group);

        return $this->request($path);
    }

    /**
     * Add channels to group
     *
     * @param string $group name
     * @param array $channels to add
     * @return array
     * @throws PubnubException
     */
    public function channelGroupAddChannel($group, $channels = array())
    {
        return $this->channelGroupUpdate($group, $channels, 'add');
    }

    /**
     * Remove channels from group
     *
     * @param string $group name
     * @param array $channels to remove
     * @return array
     * @throws PubnubException
     */
    public function channelGroupRemoveChannel($group, $channels = array())
    {
        return $this->channelGroupUpdate($group, $channels, 'remove');
    }

    private function channelGroupUpdate($group, array $channels, $action)
    {
        $channelGroup = new ChannelGroup($group);

        if (empty($channelGroup->group)) {
            throw new PubnubException('Missing Group name in channelGroupUpdate()');
        }

        if (count($channels) <= 0) {
            throw new PubnubException('Empty channels array in channelGroupUpdate()');
        }

        $path = array(
            "v1",
            "channel-registration",
            "sub-key",
            $this->SUBSCRIBE_KEY
        );

        if (!empty($channelGroup->namespace)) {
            array_push($path, "namespace", $channelGroup->namespace);
        }

        array_push($path, "channel-group", $channelGroup->group);

        return $this->request($path, array(
            $action => join(',', $channels)
        ));
    }


    /**
     * Get the list of groups
     *
     * @param string|null $namespace name
     * @return array
     */
    public function channelGroupListGroups($namespace = null)
    {
        $path = array(
            "v1",
            "channel-registration",
            "sub-key",
            $this->SUBSCRIBE_KEY
        );

        if (!empty($namespace)) {
            array_push($path, "namespace", $namespace);
        }

        array_push($path, "channel-group");

        return $this->request($path);
    }

    /**
     * Removes group from namespace
     *
     * @param string $group name in format namespace:group
     * @return array
     */
    public function channelGroupRemoveGroup($group)
    {
        $channelGroup = new ChannelGroup($group);

        $path = array(
            "v1",
            "channel-registration",
            "sub-key",
            $this->SUBSCRIBE_KEY
        );

        if (!empty($channelGroup->namespace)) {
            array_push($path, "namespace", $channelGroup->namespace);
        }

        array_push($path, "channel-group", $channelGroup->group, "remove");

        return $this->request($path);
    }

    /**
     * Get the list of namespaces
     *
     * @return array|null
     * @throws PubnubException
     */
    public function channelGroupListNamespaces()
    {
        return $this->request(array(
            "v1",
            "channel-registration",
            "sub-key",
            $this->SUBSCRIBE_KEY,
            "namespace"
        ));
    }

    /**
     * Remove namespace
     *
     * @param string $namespace name
     * @return array|null
     * @throws PubnubException
     */
    public function channelGroupRemoveNamespace($namespace)
    {
        return $this->request(array(
            "v1",
            "channel-registration",
            "sub-key",
            $this->SUBSCRIBE_KEY,
            "namespace",
            $namespace,
            "remove"
        ));
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
     * @param $read
     * @param $manage
     * @param string|null $channelGroup
     * @param string|null $auth_key
     * @param int|null $ttl
     *
     * @return array|null
     * @throws PubnubException
     */
    public function pamGrantChannelGroup($read, $manage, $channelGroup = null, $auth_key = null, $ttl = null)
    {
        $request_params = $this->pam()->pamGrantChannelGroup($read, $manage, $channelGroup, $auth_key, $ttl, $this->SESSION_UUID);

        return $this->request($request_params['url'], $request_params['search'], false);
    }

    /**
     * @param string|null $channelGroup
     * @param string|null $auth_key
     *
     * @return array|null
     * @throws PubnubException
     */
    public function pamAuditChannelGroup($channelGroup = null, $auth_key = null)
    {

        $request_params = $this->pam()->pamAuditChannelGroup($channelGroup, $auth_key, $this->SESSION_UUID);

        return $this->request($request_params['url'], $request_params['search'], false);
    }

    /**
     * @param null $channelGroup
     * @param null $auth_key
     *
     * @return array|null
     * @throws PubnubException
     */
    public function pamRevokeChannelGroup($channelGroup = null, $auth_key = null)
    {

        $request_params = $this->pam()->pamRevokeChannelGroup($channelGroup, $auth_key, $this->SESSION_UUID);

        return $this->request($request_params['url'], $request_params['search'], false);
    }

    /**
     * Pipelines multiple requests into a single connection.
     * For PHP <= 5.3 use pipelineStart() and pipelineEnd() functions instead.
     *
     * @param Callback $callback
     * @return array
     */
    public function pipeline($callback)
    {
        $this->pipelinedFlag = true;
        $callback($this);
        $result = $this->pipelinedClient->execute();
        $this->pipelinedFlag = false;

        return $result;
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
        $result = $this->pipelinedClient->execute();
        $this->pipelinedFlag = false;

        return $result;
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

    /**
     * Set timeout for non-subscribe requests using  CURLOPT_TIMEOUT
     *
     * @param int $timeout in seconds
     */
    public function setTimeout($timeout) {
        $this->defaultClient->setTimeout($timeout);
        $this->pipelinedClient->setTimeout($timeout);
    }

    /**
     * Set timeout for subscribe requests using  CURLOPT_TIMEOUT
     *
     * @param int $timeout in seconds
     */
    public function setSubscribeTimeout($timeout) {
        $this->defaultClient->setSubscribeTimeout($timeout);
        $this->pipelinedClient->setSubscribeTimeout($timeout);
    }

    private function leave($channel)
    {
        $this->request(array(
            'v2',
            'presence',
            'sub_key',
            $this->SUBSCRIBE_KEY,
            'channel',
            PubnubUtil::url_encode($channel),
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
     * @param bool $throw
     * @return array|null
     * @throws PubnubException
     */
    private function request(array $path, array $query = array(), $useDefaultQueryArray = true, $throw = false)
    {
        if ($useDefaultQueryArray) {
            $query = array_merge($query, $this->defaultQueryArray());
        }

        try {
            if ($this->pipelinedFlag === true) {
                $this->pipelinedClient->add($path, $query);

                return null;
            } else {
                return $this->defaultClient->add($path, $query);
            }
        } catch (PubnubException $e) {
            if ($throw) {
                throw $e;
            } else {
                $this->handleError($e);
                return null;
            }
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

        $query['uuid'] = PubnubUtil::url_encode($this->SESSION_UUID);
        $query['pnsdk'] = PubnubUtil::url_encode(self::PNSDK);

        if (!empty($this->AUTH_KEY)) {
            $query['auth'] = PubnubUtil::url_encode($this->AUTH_KEY);
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
        $errorMsg = 'Error on line ' . $error->getLine() . ' in ' . $error->getFile() . ": " . $error->getMessage();
        trigger_error($errorMsg, E_USER_WARNING);
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
