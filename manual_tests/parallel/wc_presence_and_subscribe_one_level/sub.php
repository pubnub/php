<?php

require_once getenv("PUBNUB_PATH") . "/manual_tests/parallel/init.php";

echo "# WC Presence and Subscribe at one level...";

$i = 0;

$pubnub->subscribe("foo.*,foo.*-pnpres", function ($response) use ($pubnub, &$i) {

    if (array_key_exists("message", $response) && $i == 0) {
        assertEquals('join', $response['message']['action']);
        assertEquals($pubnub->getUUID(), $response['message']['uuid']);
        $i = 1;
        return true;
    } elseif (array_key_exists("message", $response) && $i == 1) {
        assertEquals('join', $response['message']['action']);
        assertNotEquals($pubnub->getUUID(), $response['message']['uuid']);
        $pubnub->publish("foo.bar", "stop");
        $i = 2;
        return true;
    } elseif ($i == 2) {
        // NOTICE: Sometimes presence event is received before message. Just rerun this test.
        $i = 3;
        return true;
    } elseif (array_key_exists("message", $response) && $i == 3) {
        assertEquals('leave', $response['message']['action']);
        assertNotEquals($pubnub->getUUID(), $response['message']['uuid']);

        echo " OK\n";
        return false;
    } else {
        echo " Failed\nUnhandled response:\n";
        print_r($response);
        return false;
    }
});

