<?php
 
require_once __DIR__.'/../lib/autoloader.php';

use Pubnub\Pubnub;


$pubnub    = new Pubnub( 'demo', 'demo', false , false, false, 'IUNDERSTAND.pubnub.com');
$here      = $pubnub->here_now(array( 'channel' => 'php_here_now' ));

$occupancy = $here['occupancy'];
$user_ids  = $here['uuids'];

print("UUIDs (userIDs): ");
print_r($user_ids);
print("\n");
print("OCCUPANTS: $occupancy\n\n");
