<?php

final class tasksApiAttachmentDto implements JsonSerializable
{
    use tasksApiJsonSerializableTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var ?int
     */
    private $log_id;

    /**
     * @var string
     */
    private $create_datetime;

    /**
     * @var tasksApiContactDto
     */
    private $contact;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $ext;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $downloadUrl;

    /**
     * @var bool
     */
    private $isImage;

    /**
     * @var string|null
     */
    private $previewUrl;

    /**
     * tasksApiAttachmentDto constructor.
     *
     * @param int                $id
     * @param int|null           $log_id
     * @param string             $create_datetime
     * @param tasksApiContactDto $contact
     * @param string             $name
     * @param int                $size
     * @param string             $ext
     * @param string             $code
     * @param string             $downloadUrl
     */
    public function __construct(
        int $id,
        ?int $log_id,
        string $create_datetime,
        tasksApiContactDto $contact,
        string $name,
        int $size,
        string $ext,
        string $code,
        string $downloadUrl,
        bool $isImage,
        ?string $previewUrl
    ) {
        $this->id = $id;
        $this->log_id = $log_id;
        $this->create_datetime = $create_datetime;
        $this->contact = $contact;
        $this->name = $name;
        $this->size = $size;
        $this->ext = $ext;
        $this->code = $code;
        $this->downloadUrl = $downloadUrl;
        $this->isImage = $isImage;
        $this->previewUrl = $previewUrl;
    }
}