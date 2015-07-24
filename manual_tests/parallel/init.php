<?php
declare(ticks = 1);
require_once getenv("PUBNUB_PATH") . "/composer/lib/autoloader.php";
require_once getenv("PUBNUB_PATH") . "/manual_tests/parallel/helpers.php";
require_once getenv("PUBNUB_PATH") . "/manual_tests/parallel/assert.php";

$pubnub = new Pubnub\Pubnub([
    "publish_key" =>"ds",
    "subscribe_key" => "ds",
    "ssl" => false
]);

$pubnub_ssl = new Pubnub\Pubnub([
    "publish_key" =>"ds",
    "subscribe_key" => "ds",
    "ssl" => true
]);

$pubnub_enc = new Pubnub\Pubnub([
    "publish_key" =>"ds",
    "subscribe_key" => "ds",
    "cipher_key" => "enigma"
]);
