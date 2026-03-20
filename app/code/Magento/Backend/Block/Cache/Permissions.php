<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Cache;

use Magento\Framework\Authorization_Interface;
use Magento\Framework\View\Element\Block\Argument_Interface;
/**
 * Class Permissions
 */
class Permissions implements Argument_Interface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;
    /**
     * Permissions constructor.
     *
     * @param AuthorizationInterface $authorization
     */
    public function __construct(Authorization_Interface $authorization)
    {
        $this->authorization = $authorization;
    }
    /**
     * @return bool
     */
    public function has_access_to_flush_catalog_images()
    {
        return $this->authorization->is_allowed('Magento_Backend::flush_catalog_images');
    }
    /**
     * @return bool
     */
    public function has_access_to_flush_js_css()
    {
        return $this->authorization->is_allowed('Magento_Backend::flush_js_css');
    }
    /**
     * @return bool
     */
    public function has_access_to_flush_static_files()
    {
        return $this->authorization->is_allowed('Magento_Backend::flush_static_files');
    }
    /**
     * @return bool
     */
    public function has_access_to_additional_actions()
    {
        return $this->has_access_to_flush_catalog_images() || $this->has_access_to_flush_js_css() || $this->has_access_to_flush_static_files();
    }
}