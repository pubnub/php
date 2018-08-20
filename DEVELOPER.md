# Developers 
## Supported PHP versions
We support PHP >= 5.6 + hhvm.

## Supported platforms
We maintain and test our SDK using Travis.CI and Ubuntu.
Windows/MacOS/BSD platforms support was verified only once after SDK v4.0 release. We do not test the new releases with these platforms. 

## Subscription
The important thing to notice is that the subscription loop in PHP SDK is synchronous.
You can find that PHP support threads and event loop libraries, but all of them are not stable. Anyway, the language wasn't designed for asynchronous tasks. So the main purpose for PHP SDK is invoking the synchronous endpoint calls.

## 3rd Party Libraries
### Requests
Requests library [https://github.com/rmccue/Requests] is a wrapper over raw cURL requests.

### Monolog (logging library)
We should review Monolog usage and remove the dependency if possible. Developers who don't use composer encountering problems with manual installation, so the better solution is to get rid of this extra dependency and provide another logging solution.

## Tests
There are 3 type of tests:
* Unit tests
* Functional
* Integration

We use PHPUnit framework for all test types.
