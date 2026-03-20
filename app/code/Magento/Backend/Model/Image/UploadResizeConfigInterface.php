<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Image;

/**
 * Interface UploadResizeConfigInterface
 *
 * Used to retrieve configuration for frontend image uploader
 *
 * @api
 */
interface Upload_Resize_Config_Interface
{
    /**
     * Get maximal width value for resized image
     *
     * @return int
     */
    public function get_max_width(): int;
    /**
     * Get maximal height value for resized image
     *
     * @return int
     */
    public function get_max_height(): int;
    /**
     * Get config value for frontend resize
     *
     * @return bool
     */
    public function is_resize_enabled(): bool;
}