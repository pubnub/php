<?php
require_once('Pubnub.php');

$pubnub    = new Pubnub( 'demo', 'demo', false , false, false );
$args = array('count' => 2, 'channel' => 'a', 'include_tt' => true);
$response = $pubnub->detailedHistory($args);

print_r($response);

?>
