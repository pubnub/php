<?php

/**
 * Run this script and publish any message to the "foo.bar" channel via
 * Pubnub Console: http://www.pubnub.com/console/
 *
 * This message should be received in subscribe callback.
 */
require_once "../composer/lib/autoloader.php";

$pubnub = new Pubnub\Pubnub([
    "publish_key" =>"demo-36",
    "subscribe_key" => "demo-36"
]);

$pubnub->subscribe(["foo.*"], function ($message) {
    echo "Channel message:\n";
    print_r($message);
});
