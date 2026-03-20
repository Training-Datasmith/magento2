<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Data;

use Magento\Framework\Api\Extensible_Data_Interface;
/**
 * Video Content data interface
 *
 * @api
 * @since 100.0.2
 */
interface Video_Content_Interface extends Extensible_Data_Interface
{
    public const TYPE = 'media_type';
    public const PROVIDER = 'video_provider';
    public const URL = 'video_url';
    public const TITLE = 'video_title';
    public const DESCRIPTION = 'video_description';
    public const METADATA = 'video_metadata';
    /**
     * Retrieve MIME type
     *
     * @return string
     */
    public function get_media_type();
    /**
     * Set MIME type
     *
     * @param string $mimeType
     * @return $this
     */
    public function set_media_type($mime_type);
    /**
     * Get provider
     *
     * @return string
     */
    public function get_video_provider();
    /**
     * Set provider
     *
     * @param string $data
     * @return $this
     */
    public function set_video_provider($data);
    /**
     * Get video URL
     *
     * @return string
     */
    public function get_video_url();
    /**
     * Set video URL
     *
     * @param string $data
     * @return $this
     */
    public function set_video_url($data);
    /**
     * Get Title
     *
     * @return string
     */
    public function get_video_title();
    /**
     * Set Title
     *
     * @param string $data
     * @return $this
     */
    public function set_video_title($data);
    /**
     * Get video Description
     *
     * @return string
     */
    public function get_video_description();
    /**
     * Set video Description
     *
     * @param string $data
     * @return $this
     */
    public function set_video_description($data);
    /**
     * Get Metadata
     *
     * @return string
     */
    public function get_video_metadata();
    /**
     * Set Metadata
     *
     * @param string $data
     * @return $this
     */
    public function set_video_metadata($data);
}