<?php
 
require_once __DIR__ . '/../lib/autoloader.php';

use Pubnub\Pubnub;



$publish_key = isset($argv[1]) ? $argv[1] : 'demo';
$subscribe_key = isset($argv[2]) ? $argv[2] : 'demo';
$secret_key = isset($argv[3]) ? $argv[3] : false;
$cipher_key = isset($argv[4]) ? $argv[4] : false;
$ssl_on = false;

## Create Pubnub Object

$pubnub = new Pubnub($publish_key, $subscribe_key, $secret_key, $cipher_key, $ssl_on, 'IUNDERSTAND.pubnub.com');

## Define Messaging Channel

$channel = "php_exit";

## Subscribe Example

echo("\nWaiting for Publish message... Hit CTRL+C to finish.\n");

$pubnub->subscribe(array(
    'channel' => $channel,

    'callback' => function ($message) {
            print_r($message);
            echo "\r\n";

            return false;
        }

));

