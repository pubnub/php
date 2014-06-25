###YOU MUST HAVE A PUBNUB ACCOUNT TO USE THE API.
###http://www.pubnub.com/account

## PubNub 3.5 Real-time Cloud Push API - PHP

www.pubnub.com - PubNub Real-time Push Service in the Cloud. 
http://www.pubnub.com/blog/php-push-api-walkthrough/

PubNub is a Massively Scalable Real-time Service for Web and Mobile Games. This is a cloud-based service for broadcasting Real-time messages to thousands of web and mobile clients simultaneously.

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
$pubnub = new Pubnub(
    "demo",  ## PUBLISH_KEY
    "demo",  ## SUBSCRIBE_KEY
    "",      ## SECRET_KEY
    false    ## SSL_ON?
);
```
or you can use named array:

```php
$pubnub = new Pubnub(array(
    'subscribe_key' => 'demo',
    'publish_key' => 'demo',
    'uuid' => 'my_uu_id',
    'ssl' => true
));
```
#### Send Message (PUBLISH)
```php
$info = $pubnub->publish('my_channel', 'Hey World!'));

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
$here_now = $pubnub->hereNow('my_channel');

print_r($here_now);
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
  
  
