<?php

namespace PubNub\Models\Consumer\Objects\Channel;

class PNSetChannelMetadataResult
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $description;

    /** @var array */
    protected $custom;

    /** @var ?string */
    protected ?string $eTag;

    /**
     * PNSetChannelMetadataResult constructor.
     * @param string $id
     * @param string $name
     * @param string $description
     * @param array $custom
     * @param ?string $eTag
     */
    public function __construct($id, $name, $description, $custom = null, ?string $eTag = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->custom = $custom;
        $this->eTag = $eTag;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return object
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @return ?string
     */
    public function getETag(): ?string
    {
        return $this->eTag;
    }

    public function __toString()
    {
        $custom_string = "";

        foreach ($this->custom as $key => $value) {
            if (strlen($custom_string) > 0) {
                $custom_string .= ", ";
            }

            $custom_string .=  "$key: $value";
        }

        return sprintf(
            "Channel metadata set: id: %s, name: %s, description: %s, custom: %s, eTag: %s",
            $this->id,
            $this->name,
            $this->description,
            "[" . $custom_string . "]",
            $this->eTag
        );
    }

    /**
     * @param array $payload
     * @return PNSetChannelMetadataResult
     */
    public static function fromPayload(array $payload)
    {
        $data = $payload["data"];
        $id = null;
        $name = null;
        $description = null;
        $custom = null;
        $eTag = null;

        if (array_key_exists("id", $data)) {
            $id = $data["id"];
        }

        if (array_key_exists("name", $data)) {
            $name = $data["name"];
        }

        if (array_key_exists("description", $data)) {
            $description = $data["description"];
        }

        if (array_key_exists("custom", $data)) {
            $custom = (object)$data["custom"];
        }

        if (array_key_exists("eTag", $data)) {
            $eTag = $data["eTag"];
        }
        return new PNSetChannelMetadataResult($id, $name, $description, (object)$custom, $eTag);
    }
}
