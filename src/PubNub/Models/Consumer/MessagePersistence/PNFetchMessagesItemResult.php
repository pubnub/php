<?php

namespace PubNub\Models\Consumer\MessagePersistence;

class PNFetchMessagesItemResult
{
    protected mixed $message = null;
    protected ?string $timetoken = null;
    protected mixed $metadata = null;
    protected mixed $actions = null;
    protected ?string $uuid = null;
    protected ?string $messageType = null;
    protected ?string $customMessageType = null;


    public function __construct(mixed $message, string $timetoken)
    {
        $this->message = $message;
        $this->timetoken = $timetoken;
    }

    public function setMetadata(mixed $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function setActions(mixed $actions): self
    {
        $this->actions = $actions;
        return $this;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function setMessageType(string $messageType): self
    {
        $this->messageType = $messageType;
        return $this;
    }

    public function setCustomMessageType(string $customMessageType): self
    {
        $this->customMessageType = $customMessageType;
        return $this;
    }

    public function getMessage(): mixed
    {
        return $this->message;
    }

    public function getTimetoken(): ?string
    {
        return $this->timetoken;
    }

    public function getMetadata(): mixed
    {
        return $this->metadata;
    }

    public function getActions(): mixed
    {
        return $this->actions;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getMessageType(): ?string
    {
        return $this->messageType;
    }

    public function getCustomMessageType(): ?string
    {
        return $this->customMessageType;
    }

    public static function fromJson($json, $crypto): static
    {
        $message = $json['message'];
        if ($crypto) {
            $message = $crypto->decrypt($message);
        }
        $item = new static(
            $message,
            $json['timetoken'],
        );

        if (isset($json['uuid'])) {
            $item->setUuid($json['uuid']);
        }

        if (isset($json['message_type'])) {
            $item->setMessageType($json['message_type']);
        }

        if (isset($json['custom_message_type'])) {
            $item->setCustomMessageType($json['custom_message_type']);
        }

        if (isset($json['meta'])) {
            $item->setMetadata($json['meta']);
        }

        if (isset($json['actions'])) {
            $item->setActions($json['actions']);
        } else {
            $item->setActions([]);
        }

        return $item;
    }

    public function __toString(): string
    {
        return sprintf("Fetch message item with tt: %s and content: %s", $this->timetoken, $this->message);
    }
}
