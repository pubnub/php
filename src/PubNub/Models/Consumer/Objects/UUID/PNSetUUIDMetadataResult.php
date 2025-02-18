<?php

namespace PubNub\Models\Consumer\Objects\UUID;

class PNSetUUIDMetadataResult
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $externalId;

    /** @var string */
    protected $profileUrl;

    /** @var string */
    protected $email;

    /** @var array */
    protected $custom;

    protected ?string $eTag;

    /**
     * PNSetUUIDMetadataResult constructor.
     * @param string $id
     * @param string $name
     * @param array $externalId
     * @param array $profileUrl
     * @param array $email
     * @param array $custom
     * @param ?string $eTag
     */
    public function __construct($id, $name, $externalId, $profileUrl, $email, $custom = null, $eTag = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->externalId = $externalId;
        $this->profileUrl = $profileUrl;
        $this->email = $email;
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
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @return string
     */
    public function getProfileUrl()
    {
        return $this->profileUrl;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getETag(): ?string
    {
        return $this->eTag;
    }

    /**
     * @return object
     */
    public function getCustom()
    {
        return $this->custom;
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
            "UUID metadata set: id: %s, name: %s, externalId: %s, profileUrl: %s, email: %s, custom: %s",
            $this->id,
            $this->name,
            $this->externalId,
            $this->profileUrl,
            $this->email,
            "[" . $custom_string . "]"
        );
    }

    /**
     * @param array $payload
     * @return PNSetUUIDMetadataResult
     */
    public static function fromPayload(array $payload)
    {
        $data = $payload["data"];
        $id = null;
        $name = null;
        $externalId = null;
        $profileUrl = null;
        $email = null;
        $custom = null;
        $eTag = null;

        if (array_key_exists("id", $data)) {
            $id = $data["id"];
        }

        if (array_key_exists("name", $data)) {
            $name = $data["name"];
        }

        if (array_key_exists("externalId", $data)) {
            $externalId = $data["externalId"];
        }

        if (array_key_exists("profileUrl", $data)) {
            $profileUrl = $data["profileUrl"];
        }

        if (array_key_exists("email", $data)) {
            $email = $data["email"];
        }

        if (array_key_exists("custom", $data)) {
            $custom = (object)$data["custom"];
        }

        if (array_key_exists("eTag", $data)) {
            $eTag = $data["eTag"];
        }

        return new PNSetUUIDMetadataResult($id, $name, $externalId, $profileUrl, $email, (object) $custom, $eTag);
    }
}
