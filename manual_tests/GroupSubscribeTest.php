<?php

/**
 * Run this script and publish any message to the "ch1" channel via
 * Pubnub Console: http://www.pubnub.com/console/
 *
 * This message should be received in channelGroupSubscribe callback.
 */
require_once "../composer/lib/autoloader.php";

$pubnub = new Pubnub\Pubnub("demo", "demo");

$pubnub->channelGroupAddChannel("php_manual_test", ["ch1"]);
$channels = $pubnub->channelGroupListChannels("blah");

$pubnub->channelGroupSubscribe("php_manual_test", function ($message) {
  echo "Channel group message:\n";
  print_r($message);
});
