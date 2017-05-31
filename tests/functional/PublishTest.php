<?php

namespace Tests\Functional;

use PubNub\Endpoints\PubSub\Publish;
use PubNub\Exceptions\PubNubBuildRequestException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use ReflectionMethod;


class PublishTest extends \PubNubTestCase
{
    protected static $channel = 'pubnub_php_test';

    public function testValidatesMessageNotEmpty()
    {
        $pubnub = new PubNub(new PNConfiguration());
        $publish = new Publish($pubnub);

        try {
            $publish->channel("blah")->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException$exception) {
            $this->assertEquals("Message Missing", $exception->getMessage());
        }
    }

    public function testValidatesChannelNotEmpty()
    {
        $pubnub = new PubNub(new PNConfiguration());
        $publish = new Publish($pubnub);

        try {
            $publish->message("blah")->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException $exception) {
            $this->assertEquals("Channel Missing", $exception->getMessage());
        }
    }

    public function testNonSerializable()
    {
        try {
            $this->pubnub->publish()->message(["key" => "\xB1\x31"])->channel('ch')->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubBuildRequestException $exception) {
            $this->assertEquals("Value serialization error: Malformed UTF-8 characters, possibly incorrectly encoded",
                $exception->getMessage());
        }
    }

    private function assertGeneratesCorrectPath($message, $channel, $usePost, $sequenceNumber)
    {
        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildPath');
        $r->setAccessible(true);

        $encodedMessage = PubNubUtil::urlWrite($message);

        $publish = $this->pubnub->publish();
        $publish->channel($channel);
        $publish->message($message);

        if ($usePost) {
            $publish->usePost(true);
        }

        $this->assertEquals(
            sprintf(
                $usePost ? "/publish/%s/%s/0/%s/0" : "/publish/%s/%s/0/%s/0/%s",
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                $channel,
                $encodedMessage
            ),
            $r->invoke($publish)
        );

        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildParams');
        $r->setAccessible(true);

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => $sequenceNumber,
            ],
            $r->invoke($publish)
        );
    }

    private function assertGeneratesCorrectPathUsingGet($message, $channel, $sequenceNumber)
    {
        $this->assertGeneratesCorrectPath($message, $channel, false, $sequenceNumber);
    }

    private function assertGeneratesCorrectPathUsingPost($message, $channel, $sequenceNumber)
    {
        $this->assertGeneratesCorrectPath($message, $channel, false, $sequenceNumber);
    }

    public function testPublishGet()
    {
        $this->assertGeneratesCorrectPathUsingGet(42, 34, 1);
        $this->assertGeneratesCorrectPathUsingGet('hey', 'ch', 3);
        $this->assertGeneratesCorrectPathUsingGet(42.345, 34.534, 5);
        $this->assertGeneratesCorrectPathUsingGet(true, false, 7);
        $this->assertGeneratesCorrectPathUsingGet(['hey'], 'ch',9);
    }

    public function testPublishPost()
    {
        $this->assertGeneratesCorrectPathUsingPost('hey', 'ch', 1);
        $this->assertGeneratesCorrectPathUsingPost(42, 34, 3);
        $this->assertGeneratesCorrectPathUsingPost(42.345, 34.534, 5);
        $this->assertGeneratesCorrectPathUsingPost(true, false, 7);
        $this->assertGeneratesCorrectPathUsingPost(['hey'], 'ch', 9);
    }

    public function testPublishMeta()
    {
        $channel = 'ch';
        $message = 'hey';

        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildPath');
        $r->setAccessible(true);

        $encodedMessage = PubNubUtil::urlWrite($message);
        $meta = ['m1', 'm2'];

        $publish = $this->pubnub->publish();
        $publish->channel($channel);
        $publish->message($message);
        $publish->meta($meta);

        $this->assertEquals(
            sprintf(
                "/publish/%s/%s/0/%s/0/%s",
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                $channel,
                $encodedMessage
            ),
            $r->invoke($publish)
        );

        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildParams');
        $r->setAccessible(true);

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => 1,
                "meta" => '%5B%22m1%22%2C%22m2%22%5D'
            ],
            $r->invoke($publish)
        );
    }

    public function testPublishWithStore()
    {
        $channel = 'ch';
        $message = 'hey';

        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildPath');
        $r->setAccessible(true);

        $encodedMessage = PubNubUtil::urlWrite($message);

        $publish = $this->pubnub->publish();
        $publish->channel($channel);
        $publish->message($message);
        $publish->shouldStore(true);

        $this->assertEquals(
            sprintf(
                "/publish/%s/%s/0/%s/0/%s",
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                $channel,
                $encodedMessage
            ),
            $r->invoke($publish)
        );

        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildParams');
        $r->setAccessible(true);

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => 1,
                "store" => '1',
            ],
            $r->invoke($publish)
        );
    }

    public function testPublishWithoutStore()
    {
        $channel = 'ch';
        $message = 'hey';

        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildPath');
        $r->setAccessible(true);

        $encodedMessage = PubNubUtil::urlWrite($message);

        $publish = $this->pubnub->publish();
        $publish->channel($channel);
        $publish->message($message);
        $publish->shouldStore(false);

        $this->assertEquals(
            sprintf(
                "/publish/%s/%s/0/%s/0/%s",
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                $channel,
                $encodedMessage
            ),
            $r->invoke($publish)
        );

        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildParams');
        $r->setAccessible(true);

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => 1,
                "store" => '0',
            ],
            $r->invoke($publish)
        );
    }

    public function testPublishWithAuth()
    {
        $channel = 'ch';
        $message = 'hey';

        $this->pubnub->getConfiguration()->setAuthKey("my_auth");
        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildPath');
        $r->setAccessible(true);

        $encodedMessage = PubNubUtil::urlWrite($message);

        $publish = $this->pubnub->publish();
        $publish->channel($channel);
        $publish->message($message);

        $this->assertEquals(
            sprintf(
                "/publish/%s/%s/0/%s/0/%s",
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                $channel,
                $encodedMessage
            ),
            $r->invoke($publish)
        );

        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildParams');
        $r->setAccessible(true);

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => 1,
                "auth" => 'my_auth',
            ],
            $r->invoke($publish)
        );
    }

    public function testPublishWithCipher()
    {
        $channel = 'ch';
        $message = ['hi', 'hi2', 'hi3'];

        $this->pubnub->getConfiguration()->setCipherKey("testCipher");
        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildPath');
        $r->setAccessible(true);

        $publish = $this->pubnub->publish();
        $publish->channel($channel);
        $publish->message($message);

        $this->assertEquals(
            sprintf(
                "/publish/%s/%s/0/%s/0/%s",
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                $channel,
                // NOTICE: php doesn't add spaces to stringified object,
                // so encoded string not equal ones in python or javascript
                "%22eErTQPTE1fuozhUTkDjKE08LPAz4N1fg%2Fp9RNVUF52w%3D%22"
            ),
            $r->invoke($publish)
        );

        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildParams');
        $r->setAccessible(true);

        $this->assertEquals(
            [
                "pnsdk" => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => 1,
            ],
            $r->invoke($publish)
        );
    }
}
