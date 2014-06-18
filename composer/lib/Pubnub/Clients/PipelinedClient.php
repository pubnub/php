<?php
 
namespace Pubnub\Clients;

use Pubnub\PubnubException;

/**
 * Class PipelinedClient
 *
 * Requests are collected into array.
 * Execution should be called explicitly
 *
 * @package Pubnub\Requests
 */
class PipelinedClient extends Client
{
    public function execute()
    {
        $mh = curl_multi_init();
        $chs = $this->chs();
        $stillRunning = 0;
        $result = array();

        if (function_exists('curl_multi_setopt')) {
            curl_multi_setopt($mh, CURLMOPT_PIPELINING, 1);
            curl_multi_setopt($mh, CURLMOPT_MAXCONNECTS, $this->maxConnectsSize());
        }

        foreach ($chs as $ch) {
            curl_multi_add_handle($mh, $ch);
        }

        do {
            $execReturnValue = curl_multi_exec($mh, $stillRunning);
            curl_multi_select($mh);
        } while ($stillRunning > 0);

        foreach ($chs as $i => $ch) {
            $curlError = curl_error($ch);

            if ($curlError === "") {
                $result[$i] = curl_multi_getcontent($ch);
            } else {
                throw new PubnubException ("Curl error on handle $i: $curlError\n");
            }

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);
        $this->requests = array();

        if ($execReturnValue != CURLM_OK) {
            throw new PubnubException(curl_multi_strerror($execReturnValue));
        }

        return $result;
    }

    private function maxConnectsSize()
    {
        return count($this->requests) + 1;
    }
}