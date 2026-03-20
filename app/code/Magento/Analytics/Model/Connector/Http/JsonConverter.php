<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Model\Connector\Http;

use Magento\Framework\Serialize\Serializer\Json;
/**
 * Represents JSON converter for http request and response body.
 */
class Json_Converter implements Converter_Interface
{
    /**
     * Content-Type HTTP header for json.
     * @deprecated
     * @see CONTENT_MEDIA_TYPE
     */
    public const CONTENT_TYPE_HEADER = 'Content-Type: application/json';
    /**
     * Media-Type corresponding to this converter.
     */
    public const CONTENT_MEDIA_TYPE = 'application/json';
    public function __construct(private readonly Json $serializer)
    {
    }
    /**
     * @inheritdoc
     */
    public function from_body($body)
    {
        $decoded_body = $this->serializer->unserialize($body);
        return $decoded_body ?? [$body];
    }
    /**
     * @inheritdoc
     */
    public function to_body(array $data)
    {
        return $this->serializer->serialize($data);
    }
    /**
     * @inheritdoc
     */
    public function get_content_type_header(): string
    {
        return sprintf('Content-Type: %s', self::CONTENT_MEDIA_TYPE);
    }
    /**
     * @inheritdoc
     */
    public function get_content_media_type(): string
    {
        return self::CONTENT_MEDIA_TYPE;
    }
}