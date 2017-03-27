<?php
// Enable all errors
error_reporting(-1);

require_once(__DIR__ . '/PubNubTestCase.php');
require_once(__DIR__ . '/helpers/Stub.php');
require_once(__DIR__ . '/helpers/StubTransport.php');

if (!class_exists('Thread')) {
    class Thread
    {
    }
}