<?php

require_once getenv("PUBNUB_PATH") . "/manual_tests/parallel/init.php";

echo "# WC Subscribe one level...";

$pubnub->subscribe("foo.*", function ($message) {
    assertEquals('hello', $message['message']);
    echo " OK\n";
    return false;
});
