<?php

namespace Tests\Integrational;

use PubNub\Models\Consumer\MessageActions\PNGetMessageActionResult;
use PubNub\Models\Consumer\MessageActions\PNMessageAction;
use PubNub\Models\Consumer\MessageActions\PNRemoveMessageActionResult;

class MessageActionsTest extends \PubNubTestCase
{
    public function testAddMessageAction()
    {
        $publishResult = $this->pubnub->publish()
        ->channel("pizza_talks")
        ->message("Pineapple DOES belong on pizza!")
        ->sync();

        $messageTimetoken = (int)$publishResult->getTimetoken();

        $messageAction = new PNMessageAction([
            "type" => "reaction",
            "value" => "angry_face",
            "messageTimetoken" => $messageTimetoken
        ]);

        $addMessageActionResult = $this->pubnub->addMessageAction()
            ->channel("pizza_talks")
            ->messageAction($messageAction)
            ->sync();

        $this->assertNotNull($addMessageActionResult);
        $this->assertInstanceOf(PNMessageAction::class, $addMessageActionResult);
        $this->assertEquals("reaction", $addMessageActionResult->type);
        $this->assertEquals("angry_face", $addMessageActionResult->value);
        $this->assertEquals($messageTimetoken, $addMessageActionResult->messageTimetoken);
    }

    public function testGetMessageAction()
    {
        $publishResult = $this->pubnub->publish()
        ->channel("pizza_talks")
        ->message("Pineapple DOES belong on pizza!")
        ->sync();

        $messageTimetoken = (int)$publishResult->getTimetoken();

        $messageAction = new PNMessageAction([
            "type" => "reaction",
            "value" => "angry_face",
            "messageTimetoken" => $messageTimetoken
        ]);

        $getMessageActionResult = $this->pubnub->getMessageAction()
            ->channel("pizza_talks")
            ->sync();

        $this->assertNotNull($getMessageActionResult);
        $this->assertInstanceOf(PNGetMessageActionResult::class, $getMessageActionResult);

        $messageAction = $getMessageActionResult->actions[0];
        $this->assertInstanceOf(PNMessageAction::class, $messageAction);
        $this->assertEquals("reaction", $messageAction->type);
        $this->assertEquals("angry_face", $messageAction->value);
    }

    public function testDeleteMessageAction()
    {
        $getMessageActionResult = $this->pubnub->getMessageAction()
            ->channel("pizza_talks")
            ->sync();

        $this->assertNotNull($getMessageActionResult);
        $this->assertInstanceOf(PNGetMessageActionResult::class, $getMessageActionResult);

        foreach ($getMessageActionResult->actions as $action) {
            $removeMessageActionResult = $this->pubnub->removeMessageAction()
                ->channel("pizza_talks")
                ->messageTimetoken($action->messageTimetoken)
                ->actionTimetoken($action->actionTimetoken)
                ->sync();

            $this->assertNotNull($removeMessageActionResult);
            $this->assertInstanceOf(PNRemoveMessageActionResult::class, $removeMessageActionResult);
        }
    }
}
