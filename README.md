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
            "pubnub/pubnub": "v3.5.2"
        }
    }
    ```
  2. Run ```composer install``` from command line

### PHP API
```php
$pubnub = new Pubnub(
    "demo",  ## PUBLISH_KEY
    "demo",  ## SUBSCRIBE_KEY
    "",      ## SECRET_KEY
    false    ## SSL_ON?
);
```

#### Send Message (PUBLISH)
```php
$info = $pubnub->publish(array(
    'channel' => 'hello_world', ## REQUIRED Channel to Send
    'message' => 'Hey World!'   ## REQUIRED Message String/Array
));
print_r($info);
```

#### Request Server Time (TIME)
```php
$timestamp = $pubnub->time();
var_dump($timestamp);            ## Prints integer timestamp.
```

#### Receive Message (SUBSCRIBE)
```php
$pubnub->subscribe(array(
    'channel'  => 'hello_world',        ## REQUIRED Channel to Listen
    'callback' => function($message) {  ## REQUIRED Callback With Response
        var_dump($message);  ## Print Message
        return true;         ## Keep listening (return false to stop)
    }
));
```

#### Realtime Join/Leave Events (Presence)
```php
$pubnub->presence(array(
    'channel'  => $channel,
    'callback' => function($message) {
        print_r($message);
		echo "\r\n";
        return true;
    }
));
```

#### On-demand Occupancy Status (here_now)
```php
$here_now = $pubnub->here_now(array(
    'channel' => $channel
));
```

#### Detailed History (detailedHistory())
```php
$history = $pubnub->detailedHistory(array(
    'channel' => $channel,
    'count'   => 10,
    'end'   => "13466530169226760"
));
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
  $pubnub->subscribe(array(
      'channel'  => 'hello_world',
      'callback' => function($message) {
          var_dump($message);
          return true;
      }
  ));
  ```

  but **anonymous functions** are implemented starting only PHP 5.3 version, so for legacy client you should use **create_function()** function:

  ```php
  $pubnub->subscribe(array(
      'channel'  => 'hello_world',
      'callback' => create_function(
          '$message',
          'var_dump($message); return true;'
      )
  ));
  ```
## How to build
  For building information see README.md file in **core** folder.
  
  
