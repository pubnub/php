<?php

namespace Tests\Functional;

use PubNub\Endpoints\PubSub\Publish;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\PubNubException;
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
            $publish->setChannel("blah")->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubException $exception) {
            $this->assertEquals("Message Missing.", $exception->getPubnubError()->getMessage());
        }
    }

    public function testValidatesChannelNotEmpty()
    {
        $pubnub = new PubNub(new PNConfiguration());
        $publish = new Publish($pubnub);

        try {
            $publish->setMessage("blah")->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubException $exception) {
            $this->assertEquals("Channel Missing.", $exception->getPubnubError()->getMessage());
        }
    }

    private function assertGeneratesCorrectPath($message, $channel, $usePost)
    {
        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildPath');
        $r->setAccessible(true);

        $encodedMessage = PubNubUtil::urlWrite($message);

        $publish = $this->pubnub->publish();
        $publish->setChannel($channel);
        $publish->setMessage($message);

        if ($usePost) {
            $publish->setUsePost(true);
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
                "pnsdk" => $this->pubnub->getFullName(),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => null,
                "norep" => 'true'
            ],
            $r->invoke($publish)
        );
    }

    private function assertGeneratesCorrectPathUsingGet($message, $channel)
    {
        $this->assertGeneratesCorrectPath($message, $channel, false);
    }

    private function assertGeneratesCorrectPathUsingPost($message, $channel)
    {
        $this->assertGeneratesCorrectPath($message, $channel, false);
    }

    public function testPublishGet()
    {
        $this->assertGeneratesCorrectPathUsingGet(42, 34);
        $this->assertGeneratesCorrectPathUsingGet('hey', 'ch');
        $this->assertGeneratesCorrectPathUsingGet(42.345, 34.534);
        $this->assertGeneratesCorrectPathUsingGet(true, false);
        $this->assertGeneratesCorrectPathUsingGet(['hey'], 'ch');
    }

    public function testPublishPost()
    {
        $this->assertGeneratesCorrectPathUsingPost('hey', 'ch');
        $this->assertGeneratesCorrectPathUsingPost(42, 34);
        $this->assertGeneratesCorrectPathUsingPost(42.345, 34.534);
        $this->assertGeneratesCorrectPathUsingPost(true, false);
        $this->assertGeneratesCorrectPathUsingPost(['hey'], 'ch');
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
        $publish->setChannel($channel);
        $publish->setMessage($message);
        $publish->setMeta($meta);

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
                "pnsdk" => $this->pubnub->getFullName(),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => null,
                "norep" => 'true',
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
        $publish->setChannel($channel);
        $publish->setMessage($message);
        $publish->setShouldStore(true);

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
                "pnsdk" => $this->pubnub->getFullName(),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => null,
                "norep" => 'true',
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
        $publish->setChannel($channel);
        $publish->setMessage($message);
        $publish->setShouldStore(false);

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
                "pnsdk" => $this->pubnub->getFullName(),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => null,
                "norep" => 'true',
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
        $publish->setChannel($channel);
        $publish->setMessage($message);

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
                "pnsdk" => $this->pubnub->getFullName(),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => null,
                "norep" => 'true',
                "auth" => 'my_auth',
            ],
            $r->invoke($publish)
        );
    }

    public function xtestPublishWithCipher()
    {
        $channel = 'ch';
        $message = ['hi', 'hi2', 'hi3'];

        $this->pubnub->getConfiguration()->setCipherKey("testCipher");
        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildPath');
        $r->setAccessible(true);

        $publish = $this->pubnub->publish();
        $publish->setChannel($channel);
        $publish->setMessage($message);

        $this->assertEquals(
            sprintf(
                "/publish/%s/%s/0/%s/0/%s",
                $this->pubnub->getConfiguration()->getPublishKey(),
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                $channel,
                "%22FQyKoIWWm7oN27zKyoU0bpjpgx49JxD04EI%2F0a8rg%2Fo%3D%22"
            ),
            $r->invoke($publish)
        );

        $r = new ReflectionMethod('\PubNub\Endpoints\PubSub\Publish', 'buildParams');
        $r->setAccessible(true);

        $this->assertEquals(
            [
                "pnsdk" => $this->pubnub->getFullName(),
                "uuid" => $this->pubnub->getConfiguration()->getUuid(),
                "seqn" => null,
                "norep" => 'true'
            ],
            $r->invoke($publish)
        );
    }
}
