<?php
 
namespace Pubnub;


class PubnubPAM
{
    private $publish_key = null;
    private $subscribe_key = null;
    private $secret_key = null;
    private $pnsdk = null;

    function __construct($publish_key, $subscribe_key, $secret_key, $pnsdk)
    {
        $this->publish_key = $publish_key;
        $this->subscribe_key = $subscribe_key;
        $this->secret_key = $secret_key;
        $this->pnsdk = $pnsdk;
    }

    public function sign($message)
    {
        return strtr(base64_encode(hash_hmac(
            'sha256',
            utf8_encode($message),
            utf8_encode($this->secret_key),
            true
        )), '+/', '-_' );
    }

    /**
     * @param String $channel
     * @param Boolean $read
     * @param Boolean $write
     * @param String $auth_key
     * @param Integer $ttl
     *
     * @return array
     */
    public function grant($read, $write, $channel, $auth_key, $ttl)
    {
        return $this->getRequestParams(array(
            'method' => 'grant',
            'channel' => $channel,
            'read' => $read,
            'write' => $write,
            'auth_key' => $auth_key,
            'ttl' => $ttl
        ));
    }

    /**
     * @param boolean $read
     * @param boolean $manage
     * @param String $channelGroup
     * @param String $auth_key
     * @param Integer $ttl
     *
     * @return array
     */
    public function pamGrantChannelGroup($read, $manage, $channelGroup, $auth_key, $ttl) {

        return $this->getRequestParams(array(
            'method' => 'grant',
            'channel-group' => $channelGroup,
            'read' => $read,
            'manage' => $manage,
            'auth_key' => $auth_key,
            'ttl' => $ttl
        ));
    }

    /**
     * @param String $channel
     * @param String $auth_key
     *
     * @return array
     */
    public function audit($channel, $auth_key)
    {
        return $this->getRequestParams(array(
            'method' => 'audit',
            'channel' => $channel,
            'auth_key' => $auth_key
        ));
    }

    /**
     * @param String $channelGroup
     * @param String $auth_key
     *
     * @return array
     */
    public function pamAuditChannelGroup( $channelGroup, $auth_key) {

        return $this->getRequestParams(array(
            'method' => 'audit',
            'channel-group' => $channelGroup,
            'auth_key' => $auth_key
        ));
    }

    /**
     * @param String $channel
     * @param String $auth_key
     *
     * @return array
     */
    public function revoke($channel, $auth_key)
    {
        return $this->grant(0, 0, $channel, $auth_key, null);
    }

    /**
     * @param String $channelGroup
     * @param String $auth_key
     *
     * @return array
     */
    public function pamRevokeChannelGroup($channelGroup, $auth_key) {

        return $this->getRequestParams(array(
            'method' => 'grant',
            'channel-group' => $channelGroup,
            'read' => "0",
            'manage' => "0",
            'auth_key' => $auth_key

        ));
    }

    /**
     * @param array $query
     *
     * @return array
     */
    private function getRequestParams(array $query)
    {
        $params = array();

        if (isset($query['auth_key']) && $query['auth_key']) {
            $params['auth'] = PubnubUtil::url_encode($query['auth_key']);
        }

        if (isset($query['channel'])) {
            $params['channel'] = PubnubUtil::url_encode($query['channel']);
        }

        if (isset($query['channel-group'])) {
            $params['channel-group'] = PubnubUtil::url_encode($query['channel-group']);
        }

        if (isset($query['manage'])) {
            $params['m'] = $query['manage'] ? 1 : 0;
        }

        $params['pnsdk'] = urlencode($this->pnsdk);

        if (isset($query['read'])) {
            $params['r'] = $query['read'] ? 1 : 0;
        }

        $params['timestamp'] = time();

        if (isset($query['ttl']) && (intval($query['ttl']) ||
                  $query['ttl'] === 0)) {
            $params['ttl'] = $query['ttl'];
        }

        if (isset($query['write'])) {
            $params['w'] = $query['write'] ? 1 : 0;
        }

        $string_to_sign = implode("\n", array(
            $this->subscribe_key,
            $this->publish_key,
            $query['method'],
            $this->paramsToString($params)
        ));

        $params['signature'] = $signature = $this->sign($string_to_sign);

        return array(
            "url" => array(
                'v1',
                'auth',
                $query['method'],
                'sub-key',
                $this->subscribe_key
            ),
            "search" => $params
        );
    }

    private function paramsToString(array $params)
    {
        $ary = array();

        foreach ($params as $key => $val) {
            $ary[] = $key . "=" . $val;
        }

        return implode('&', $ary);
    }
}
