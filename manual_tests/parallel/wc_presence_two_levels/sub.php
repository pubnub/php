<?php

require_once getenv("PUBNUB_PATH") . "/manual_tests/parallel/init.php";

echo "# WC Presence two levels...";

$pubnub->presence("foo.*", function ($response) use ($pubnub) {
    assertEquals("join", $response['message']['action']);
    $pubnub->publish("foo.bar", "stop");
    return false;
});

