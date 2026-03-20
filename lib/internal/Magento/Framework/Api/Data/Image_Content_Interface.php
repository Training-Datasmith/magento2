<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Data;

/**
 * Image Content data interface
 *
 * @api
 * @since 100.0.2
 */
interface Image_Content_Interface
{
    public const BASE64_ENCODED_DATA = 'base64_encoded_data';
    public const TYPE = 'type';
    public const NAME = 'name';
    /**
     * Retrieve media data (base64 encoded content)
     *
     * @return string
     */
    public function get_base64encoded_data();
    /**
     * Set media data (base64 encoded content)
     *
     * @param string $data
     * @return $this
     */
    public function set_base64encoded_data($data);
    /**
     * Retrieve MIME type
     *
     * @return string
     */
    public function get_type();
    /**
     * Set MIME type
     *
     * @param string $mimeType
     * @return $this
     */
    public function set_type($mime_type);
    /**
     * Retrieve image name
     *
     * @return string
     */
    public function get_name();
    /**
     * Set image name
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name);
}