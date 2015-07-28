<?php
require_once("vendor/autoload.php");

use Pubnub\Pubnub;

$config = [
    'publish_key' => 'demo',
    'subscribe_key' => 'demo'
];

if ($_GET['ssl'] == 'true') {
    $config['ssl'] = true;
    $config['pem_path'] = "./";
}

if (!empty($_GET['cipher_key'])) {
    $config['cipher_key'] = $_GET['cipher_key'];
}

$pubnub = new Pubnub($config);

$message = $_GET['message'];

if (empty($message)) {
    $message = "<empty message>";
}

$pubnub->publish("gae-php-console", $message);
