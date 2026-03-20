<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Image;

/**
 * Image uploader config provider.
 */
class Upload_Resize_Config implements Upload_Resize_Config_Interface
{
    /**
     * Config path for the maximal image width value
     */
    public const XML_PATH_MAX_WIDTH_IMAGE = 'system/upload_configuration/max_width';
    /**
     * Config path for the maximal image height value
     */
    public const XML_PATH_MAX_HEIGHT_IMAGE = 'system/upload_configuration/max_height';
    /**
     * Config path for the maximal image height value
     */
    public const XML_PATH_ENABLE_RESIZE = 'system/upload_configuration/enable_resize';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(\Magento\Framework\App\Config\Scope_Config_Interface $config)
    {
        $this->config = $config;
    }
    /**
     * Get maximal width value for resized image
     *
     * @return int
     */
    public function get_max_width(): int
    {
        return (int) $this->config->get_value(self::XML_PATH_MAX_WIDTH_IMAGE);
    }
    /**
     * Get maximal height value for resized image
     *
     * @return int
     */
    public function get_max_height(): int
    {
        return (int) $this->config->get_value(self::XML_PATH_MAX_HEIGHT_IMAGE);
    }
    /**
     * Get config value for frontend resize
     *
     * @return bool
     */
    public function is_resize_enabled(): bool
    {
        return (bool) $this->config->get_value(self::XML_PATH_ENABLE_RESIZE);
    }
}