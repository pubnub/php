## Contact support@pubnub.com for all questions

#### [PubNub](http://www.pubnub.com) Real-time Data Network

##### Clients for PHP and Composer

### How to include

#### PHP >= 5.3 without composer
  1. You need only **composer** folder. To get it clone repo:

    ``` sh
    $ git clone https://github.com/pubnub/php.git ./pubnub-php
    ```

  2. Copy **composer/lib** folder to your project and include **autoloader.php** file.
  3. Require autoloader.php:
  
    ```php
    require_once('lib/autoloader.php');
    ```
    
#### PHP >= 5.3 with composer
  1. Add **pubnub** package to your composer.json file:
  
    ``` json
    {
        "require": {
            "pubnub/pubnub": "3.7.*"
        }
    }
    ```
  2. Run ```composer install``` from command line

### PHP API
You can instantiate PubNub client using postional list of arguments:

```php
use Pubnub\Pubnub;

$pubnub = new Pubnub(
    "demo",  ## PUBLISH_KEY
    "demo",  ## SUBSCRIBE_KEY
    "",      ## SECRET_KEY
    false    ## SSL_ON?
);
```
or you can use named array:

```php
use Pubnub\Pubnub;

$pubnub = new Pubnub(array(
    'subscribe_key' => 'demo',
    'publish_key' => 'demo',
    'uuid' => 'my_uu_id',
    'ssl' => true
));
```

#### SSL mode
To enable secure connection using SSL, you should explicitly enable it in Pubnub instance initializer and specify local path to `pubnub.com.pem` file. Do not add file name to the path. Certificate file is located at the root of github PHP SDK repository. If you want to disable SSL certificate verification, you should explicitly specify it in initializer as `verify_peer` param.

###### Example 1. Enable SSL and specify .pem file location as one level up.

```
$pubnub = new Pubnub(array(
    'subscribe_key' => 'demo',
    'publish_key' => 'demo',
    'ssl' => true,
    'pem_path' => "../"
));
```

###### Example 2. Enable SSL and specify .pem file location using positional arguments.

```
$pubnub = new Pubnub("demo", "demo", false, false, false, true, false, "../");
```

###### Example 3. Enable SSL, but do not verify ceritificate file. It may not even exist. Just send requests to the secure SSL 443 port.

```
$pubnub = new Pubnub(array(
    'subscribe_key' => 'demo',
    'publish_key' => 'demo',
    'ssl' => true,
    'verify_peer' => false
));
```

###### Default SSL options values:
- ssl: false
- verify_peer: true

#### Send Message (PUBLISH)
```php
$info = $pubnub->publish('my_channel', 'Hey World!');

print_r($info);
```

#### Request Server Time (TIME)
```php
$timestamp = $pubnub->time();
var_dump($timestamp);            ## Prints integer timestamp.
```

#### Receive Message (SUBSCRIBE)
```php
$pubnub->subscribe('my_channel', function($message) {
    var_dump($message);  ## Print Message
    return true;         ## Keep listening (return false to stop)
});
```

#### Realtime Join/Leave Events (Presence)
```php
// will subscribe to *my_channel-pnpres* channel
$pubnub->presence('my_channel', function($message) {
    print_r($message);
    echo "\r\n";
    return false;
});
```

#### On-demand Occupancy Status (here_now)
```php
// all users of specific channel with uuids
$here_now = $pubnub->hereNow('my_channel');

// all users of specific channel without uuids
$here_now = $pubnub->hereNow('my_channel', true);

// all users of specific channel with state info
$here_now = $pubnub->hereNow('my_channel', false, true);

// all users of all channels for given subscribe key
$here_now = $pubnub->hereNow();

```

#### History (detailedHistory())
```php
$history = $pubnub->history('demo', 3, false, false, 13466530169226760);

print_r($history);
```
will output:
```php
Array
(
    [messages] => Array
        (
            [0] => message #1
            [1] => message #2
            [2] => message #3
        )

    [date_from] => 14037149868340218
    [date_to] => 14037149868888352
)
```
#### History (detailedHistory()) with time tokens
```php
$history = $pubnub->history('demo', 3, true);

print_r($history);
```
will output:
```php
Array
(
    [messages] => Array
        (
            [0] => Array
                (
                    [message] => message #1
                    [timetoken] => 14037149868340218
                )

            [1] => Array
                (
                    [message] => message #2
                    [timetoken] => 14037149868613433
                )

            [2] => Array
                (
                    [message] => message #3
                    [timetoken] => 14037149868888352
                )
        )
    [date_from] => 14037149868340218
    [date_to] => 14037149868888352
)
```

#### Current channels for given subscriber (whereNow)
```php
$result = $pubnub->whereNow('user_uuid');

print_r($result);
```
will output:
```php
Array
(
    [status] => 200
    [message] => OK
    [payload] => Array
        (
            [channels] => Array
                (
                    [0] => demo_channel
                )

        )

    [service] => Presence
)
```

#### User state information  (setState/getState)
```php
$pubnub->setState('demo', array('name' => 'Mike', 'status' => 'busy', 'age' => 30));
$result = $pubnub->getState('demo', $pubnub->getUUID());

print_r($result);
```
will output:
```php
Array
(
    [status] => 200
    [uuid] => DE2BE11A-9ABE-4ACE-B742-8B0508112619
    [service] => Presence
    [message] => OK
    [payload] => Array
        (
            [status] => busy
            [age] => 30
            [name] => Mike
        )

    [channel] => demo
)
```

#### PAM Grant/Audit/Revoke
Checkout channel/subkey/user-level grant/audit/revoke examples [here](https://github.com/pubnub/php/tree/master/tests/integration)


#### PHP HTTP Pipelining usage
```php
$pubnub->pipeline(function ($p) {
      $p->publish('my_channel', "Pipelined message #1");
      $p->publish('my_channel', "Pipelined message #2");
      $p->publish('my_channel', "Pipelined message #3");
});
```

#### Channel Groups management
To use namespaces just add namespace name with ":" prior to group name string.
For example in `news:music`, `news` is namespace and `music` is group
and `news_music` is top-level channel group.

```php
// Add channels to group:
$pubnub->channelGroupAddChannel("music_news", ["jazz_news", "rock_news", "punk_news"]);
$pubnub->channelGroupAddChannel("news:music", ["jazz_news", "rock_news", "punk_news"]);

// Get group channels list
$pubnub->channelGroupListChannels("music_news");
$pubnub->channelGroupListChannels("news:music");

// Remove channel from group
$pubnub->channelGroupRemoveChannel("music_news", ["rock_news"]);
$pubnub->channelGroupRemoveChannel("news:music", ["rock_news"]);

// Subscribe to channel
$pubnub->channelGroupSubscribe("music_news", function ($result) {
    print_r($result);
});
$pubnub->channelGroupSubscribe("news:music", function ($result) {
    print_r($result);
});

// Gets a list of uuids subscribed to the channel groups.
$pubnub->channelGroupHereNow("news:music");

// Remove channel group
$pubnub->channelGroupRemoveGroup("music_news");
$pubnub->channelGroupRemoveGroup("news:music");

// Remove namespace
$pubnub->channelGroupRemoveNamespace("news");

```

#### Subscribe and non-subscribe cURL timeout setters
```php
// Default non-subscribe timeout is 30 seconds
$pubnub->setTimeout(5);

// Default subscribe timeout is 310 seconds
$pubnub->setSubscribeTimeout(100);

```

#### Errors and Exceptions handling:

1. Errors are triggered using `trigger_error()` when cURL request fails with HTTP or cURL error

2. `PubnubException`'s will be thrown if you miss any param while invoking methods of Pubnub instance. For ex. you passed empty channel string to #publish() method or forgot to pass callback to #subscribe().

## Contact support@pubnub.com for all questions
