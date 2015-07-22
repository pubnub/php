<?php

require_once getenv("PUBNUB_PATH") . "/manual_tests/parallel/init.php";

sleep(2);
$pubnub->subscribe("foo.*", function ($response) {
    assertEquals("stop", $response['message']);
    echo " OK\n";
    return false;
});