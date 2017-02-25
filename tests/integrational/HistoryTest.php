<?php

use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\History\PNHistoryResult;
use \PubNub\Models\Consumer\PNPublishResult;
use PubNub\PNConfiguration;
use PubNub\PubNub;


class TestPubNubHistory extends PubNubTestCase
{
    const COUNT = 5;
    const TOTAL = 7;

    public function testBasic()
    {
        $ch = "history-php-ch";

        $this->pubnub->getConfiguration()->setUuid("history-php-uuid");

        for ($i = 0; $i < static::TOTAL; $i++) {
            $result = $this->pubnub->publish()->channel($ch)->message("hey-" . $i)->sync();
            $this->assertGreaterThan(0, $result->getTimetoken());

            $result = $this->pubnub->history()->channel($ch)->count(static::COUNT)->sync();
            $result->getMessages();
        }

        sleep(15);

        /** @var PNHistoryResult $result */
        $result = $this->pubnub->history()->channel($ch)->count(static::COUNT)->sync();

        $this->assertInstanceOf(PNHistoryResult::class, $result);
        $this->assertGreaterThan(0, $result->getStartTimetoken());
        $this->assertGreaterThan(0, $result->getEndTimetoken());
        $this->assertCount(static::COUNT, $result->getMessages());

        $result->getMessages();

        $this->assertEquals("hey-2", $result->getMessages()[0]->getEntry());
        $this->assertEquals("hey-3", $result->getMessages()[1]->getEntry());
        $this->assertEquals("hey-4", $result->getMessages()[2]->getEntry());
        $this->assertEquals("hey-5", $result->getMessages()[3]->getEntry());
        $this->assertEquals("hey-6", $result->getMessages()[4]->getEntry());
    }

    public function testEncrypted()
    {
        $ch = "history-php-ch";

        for ($i = 0; $i < static::TOTAL; $i++) {
            $result = $this->pubnub_enc->publish()->channel($ch)->message("hey-" . $i)->sync();
            self::assertInstanceOf(PNPublishResult::class, $result);
            self::assertGreaterThan(0, $result->getTimetoken());
        }

        sleep(15);

        /** @var PNHistoryResult $result */
        $result = $this->pubnub_enc->history()->channel($ch)->count(static::COUNT)->sync();

        $this->assertInstanceOf(PNHistoryResult::class, $result);
        $this->assertGreaterThan(0, $result->getStartTimetoken());
        $this->assertGreaterThan(0, $result->getEndTimetoken());
        $this->assertCount(static::COUNT, $result->getMessages());

        $this->assertEquals("hey-2", $result->getMessages()[0]->getEntry());
        $this->assertEquals("hey-3", $result->getMessages()[1]->getEntry());
        $this->assertEquals("hey-4", $result->getMessages()[2]->getEntry());
        $this->assertEquals("hey-5", $result->getMessages()[3]->getEntry());
        $this->assertEquals("hey-6", $result->getMessages()[4]->getEntry());
    }

    public function testNotPermitted()
    {
        $ch = "history-php-ch";
        $config = new PNConfiguration();
        $config->setPublishKey(static::PUBLISH_KEY_PAM);
        $config->setSubscribeKey(static::SUBSCRIBE_KEY_PAM);
        $pubnub = new PubNub($config);

        $this->expectException(PubNubServerException::class);
        $pubnub->history()->channel($ch)->count(static::COUNT)->sync();
    }

    public function testSuperCallWithChannelOnly()
    {
        $ch = "history-php-ch";

        $this->pubnub_pam->getConfiguration()->setUuid("history-php-uuid");

        $result = $this->pubnub_pam->history()->channel($ch)->sync();

        $this->assertInstanceOf(PNHistoryResult::class, $result);
    }

    public function testSuperCallWithAllParams()
    {
        $ch = "history-php-ch";

        $this->pubnub_pam->getConfiguration()->setUuid("history-php-uuid");

        $result = $this->pubnub_pam->history()
            ->channel($ch)
            ->count(2)
            ->includeTimetoken(true)
            ->reverse(true)
            ->start(1)
            ->end(2)
            ->sync();

        $this->assertInstanceOf(PNHistoryResult::class, $result);
    }
}