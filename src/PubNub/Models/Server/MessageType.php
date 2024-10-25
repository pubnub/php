<?php

namespace PubNub\Models\Server;

abstract class MessageType
{
    public const MESSAGE = 0;
    public const SIGNAL = 1;
    public const OBJECT = 2;
    public const MESSAGE_ACTION = 3;
    public const FILE_MESSAGE = 4;
}
