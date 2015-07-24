<?php

require_once getenv("PUBNUB_PATH") . "/manual_tests/parallel/init.php";

sleep(1);
$pubnub->publish("foo.bar.baz", "hello");