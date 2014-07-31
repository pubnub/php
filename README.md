## Contact support@pubnub.com for all questions

#### [PubNub](http://www.pubnub.com) Real-time Data Network

##### Clients for PHP and Composer
### Code Status

* [![Build Status](https://travis-ci.org/pubnub/php.svg?branch=master)](https://travis-ci.org/pubnub/php)

### How to include

#### PHP <= 5.2
  1. You need only **legacy** folder. To get it clone repo:

    ``` sh
    $ git clone https://github.com/pubnub/php.git ./pubnub-php
    ```
  2. Copy 2 files to your project vendor libraries folder.
  3. Require Pubnub.php file:

    ``` php
    require_once('legacy/Pubnub.php');
    ```

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
            "pubnub/pubnub": "3.5.*"
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

#### PHP HTTP Pipelining usage
```php
$pubnub->pipeline(function ($p) {
      $p->publish('my_channel', "Pipelined message #1");
      $p->publish('my_channel', "Pipelined message #2");
      $p->publish('my_channel', "Pipelined message #3");
});
```

## Differences with legacy/composer clients usage
* in composer cliend you should use namespace **Pubnub** to access Pubnub class:

  ``` php
  <?php
  use Pubnub\Pubnub;
  
  $pubnub = new Pubnub();
  ?>
  ```

* in **composer** client the most convenient way for defining collbacks is to use **anonymous functions**:

  ``` php
  $pubnub->subscribe('my_channel', function($message) {
    var_dump($message);
    return true;
  });
  ```

  but **anonymous functions** are implemented starting only PHP 5.3 version, so for legacy client you should use **create_function()** function:

  ```php
  $pubnub->subscribe('my_channel', create_function('$message', 'var_dump($message); return true;'));
  ```
## How to build
  For building information see README.md file in **core** folder.
  
## Contact support@pubnub.com for all questions
