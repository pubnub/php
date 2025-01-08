<?php

namespace Tests\Integrational;

use PubNub\Models\Consumer\MessageActions\PNAddMessageActionResult;
use PubNub\Models\Consumer\MessageActions\PNGetMessageActionResult;
use PubNub\Models\Consumer\MessageActions\PNMessageAction;
use PubNub\Models\Consumer\MessageActions\PNRemoveMessageActionResult;

class MessageActionsTest extends \PubNubTestCase
{
    protected string $channelName = "pizza_talks";
    protected int $messageTimetoken;

    public function setUp(): void
    {
        parent::setUp();
        $publishResult = $this->pubnub->publish()
        ->channel($this->channelName)
        ->message("Pineapple DOES belong on pizza!")
        ->sync();

        $this->messageTimetoken = (int)$publishResult->getTimetoken();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->pubnub->deleteMessages()
            ->channel($this->channelName)
            ->sync();
    }

    private function addTestMessageAction(): PNAddMessageActionResult
    {
        $messageAction = new PNMessageAction([
            "type" => "reaction",
            "value" => "angry_face",
            "messageTimetoken" => $this->messageTimetoken
        ]);

        $addMessageActionResult = $this->pubnub->addMessageAction()
            ->channel($this->channelName)
            ->messageAction($messageAction)
            ->sync();
        return $addMessageActionResult;
    }

    public function testAddMessageAction(): void
    {
        $addMessageActionResult = $this->addTestMessageAction();
        $this->assertNotNull($addMessageActionResult);
        $this->assertInstanceOf(PNMessageAction::class, $addMessageActionResult);
        $this->assertEquals("reaction", $addMessageActionResult->type);
        $this->assertEquals("angry_face", $addMessageActionResult->value);
        $this->assertEquals($this->messageTimetoken, $addMessageActionResult->messageTimetoken);
    }

    public function testGetMessageAction(): void
    {
        $addMessageActionResult = $this->addTestMessageAction();
        $getMessageActionsResult = $this->pubnub->getMessageActions()
            ->channel($this->channelName)
            ->sync();

        $this->assertNotNull($getMessageActionsResult);
        $this->assertInstanceOf(PNGetMessageActionResult::class, $getMessageActionsResult);
        $this->assertNotEmpty($getMessageActionsResult->actions);
        $this->assertCount(1, $getMessageActionsResult->actions);

        $messageAction = $getMessageActionsResult->actions[0];
        $this->assertInstanceOf(PNMessageAction::class, $messageAction);
        $this->assertEquals("reaction", $messageAction->type);
        $this->assertEquals("angry_face", $messageAction->value);
        $this->assertEquals($this->messageTimetoken, $messageAction->messageTimetoken);
        $this->assertEquals($addMessageActionResult->actionTimetoken, $messageAction->actionTimetoken);
    }

    public function testDeleteMessageAction(): void
    {
        $addMessageActionResult = $this->addTestMessageAction();

        $getMessageActionsResult = $this->pubnub->getMessageActions()
            ->channel($this->channelName)
            ->sync();

        $this->assertNotNull($getMessageActionsResult);
        $this->assertInstanceOf(PNGetMessageActionResult::class, $getMessageActionsResult);
        $this->assertNotEmpty($getMessageActionsResult->actions);
        $this->assertCount(1, $getMessageActionsResult->actions);

        foreach ($getMessageActionsResult->actions as $action) {
            $removeMessageActionResult = $this->pubnub->removeMessageAction()
                ->channel($this->channelName)
                ->messageTimetoken($action->messageTimetoken)
                ->actionTimetoken($action->actionTimetoken)
                ->sync();

            $this->assertNotNull($removeMessageActionResult);
            $this->assertInstanceOf(PNRemoveMessageActionResult::class, $removeMessageActionResult);
        }
    }
}
