<?php
 
namespace Pubnub\Clients;

use Pubnub\JSON;
use Pubnub\PubnubException;


/**
 * Class DefaultClient
 *
 * Requests are executed immediately
 *
 * @package Pubnub\Requests
 */
class DefaultClient extends Client
{
    public function add(array $path, array $query)
    {
        parent::add($path, $query);

        return $this->execute();
    }

    private function execute()
    {
        $chs = $this->chs();
        $ch = $chs[0];

        $output = curl_exec($ch);
        $curlError = curl_errno($ch);
        $curlResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $JSONDecodedResponse = JSON::decode($output);

        curl_close($ch);
        $this->requests = array();

        if ($JSONDecodedResponse != null)
            return $JSONDecodedResponse;
        elseif ($curlError == 28)
            throw new PubnubException("_PUBNUB_TIMEOUT");
        elseif ($curlResponseCode == 400 || $curlResponseCode == 404)
            throw new PubnubException("_PUBNUB_MESSAGE_TOO_LARGE");
    }
}