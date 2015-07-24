<?php

require_once getenv("PUBNUB_PATH") . "/manual_tests/parallel/init.php";

echo "# WC Presence one level...";

$pubnub->presence("foo.*", function ($response) use ($pubnub) {
    if (
        $response['message']['action'] == 'join'
        && $response['message']['uuid'] != $pubnub->getUUID()
    ) {
        $pubnub->publish("foo.bar", "stop");
        return false;
    } else {
        return true;
    }
});

