<?php

/**
 * Run this script and publish any message to the "php_manual_test" channel via
 * Pubnub Console: http://www.pubnub.com/console/
 *
 * This message should be received in subscribe callback.
 */
require_once "../composer/lib/autoloader.php";

$pubnub = new Pubnub\Pubnub("demo", "demo");

$pubnub->subscribe("php_manual_test", function ($message) {
  echo "Channel message:\n";
  print_r($message);
});
