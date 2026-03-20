<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\Data_Providers;

use Magento\Backend\Model\Image\Upload_Resize_Config_Interface;
use Magento\Framework\View\Element\Block\Argument_Interface;
/**
 * Provides additional data for image uploader
 */
class Image_Upload_Config implements Argument_Interface
{
    /**
     * @var UploadResizeConfigInterface
     */
    private $image_upload_config;
    /**
     * @param UploadResizeConfigInterface $imageUploadConfig
     */
    public function __construct(Upload_Resize_Config_Interface $image_upload_config)
    {
        $this->image_upload_config = $image_upload_config;
    }
    /**
     * Get image resize configuration
     *
     * @return int
     */
    public function get_is_resize_enabled(): int
    {
        return (int) $this->image_upload_config->is_resize_enabled();
    }
}