<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents converter interface for http request and response body.
 *
 * @api
 * @since 100.2.0
 */
interface Converter_Interface
{
    /**
     * Unserialize data
     *
     * @param string $body
     * @return array
     * @since 100.2.0
     */
    public function from_body($body);
    /**
     * Serialize data
     *
     * @return string
     * @since 100.2.0
     */
    public function to_body(array $data);
    /**
     * Retrieve content type
     *
     * @return string
     * @since 100.2.0
     */
    public function get_content_type_header();
    /**
     * Retrieve content media
     *
     * @since 100.3.0
     */
    public function get_content_media_type(): string;
}