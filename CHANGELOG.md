## [v4.2.0](https://github.com/pubnub/php/releases/tag/v4.2.0)
February-2-2021

- 🌟️ Add support for device channel registration with apns2. 
- 🌟️ Allows management of users and channels with metadata. 
- 🌟️ Implement v2 signatures required for push and objects. 
- 🌟️ Implement v2 grant endpoint with support for user level grant. 

## [v4.1.7](https://github.com/pubnub/php/releases/tag/v4.1.7)
September-14-2020

- 🌟️ Add delete permission support to grant call. 

## [v4.1.6](https://github.com/pubnub/php/releases/tag/v4.1.6)
August-20-2020

- ⭐️️ Remove hard coded keys from tests. 

## [v4.1.5](https://github.com/pubnub/php/tree/v4.1.5)
 October-22-2019

- ⭐Update composer.json package version constraints
- ⭐Update .travis.yml to run tests for PHP versions 7.2 and 7.3

## [v4.1.4](https://github.com/pubnub/php/tree/v4.1.4)
 October-18-2019

- 🐛Add support for request transport reusing to resolve slow publish issues when multiple messages are published consecutively.
- 🐛Drop support for HHVM.

## [v4.1.3](https://github.com/pubnub/php/tree/v4.1.3)
 February-28-2019

- ⭐Add messageCounts() method for retrieving unread message count

## [v4.1.2](https://github.com/pubnub/php/tree/v4.1.2)
 October-23-2018

- 🐛Fix issue with deleteMessages endpoint using GET HTTP method instead of DELETE

## [v4.1.1](https://github.com/pubnub/php/tree/v4.1.1)
 October-2-2018

- ⭐Add setOrigin method
- ⭐Add .gitattributes file to save space when using composer
- 🐛Fix urlencode issue with channel names
- 🐛Fix channel name validation for Publish
- 🐛Return class instance of PNConfiguration on setConnectTimeout method

## [v4.1.0](https://github.com/pubnub/php/tree/v4.1.0)
 September-7-2018

- ⭐Add fire() method on PubNub instance
- 🐛Change return value of SetState::getOperationType
- ⭐Add history delete (deleteMessages) method on PubNub instance
- ⭐Add Telemetry Manager

## [v4.0.0](https://github.com/pubnub/php/tree/v4.0.0)
 June-8-2017

- 🐛Fix publish sequence counter
- 🐛Fix publish tests
- 🐛Release final SDK

## [v4.0.0-beta.3](https://github.com/pubnub/php/tree/v4.0.0-beta.3)
 May-5-2017

- 🐛Fix special characters encoding
- 🐛Remove set* prefix from publish setters

## [v4.0.0-beta.2](https://github.com/pubnub/php/tree/v4.0.0-beta.2)
 April-21-2017

- 🐛Add missing methods on PubNub instance
- 🐛Fix removeAllPushChannelsForDevice method case

## [v4.0.0-beta](https://github.com/pubnub/php/tree/v4.0.0-beta)
 April-18-2017

- 🐛Fix windows compatibility
- ⭐Add option to disable SSL
- 🐛Fix subscribe presence response parsing
- 🐛Add missing removeListener()
- ⭐Add logger
- 🐛Fix json decoding error
- ⭐Add Push methods


## [v4.0.0-alpha](https://github.com/pubnub/php/tree/v4.0.0-alpha)
 April-5-2017

- ⭐Alpha Release
