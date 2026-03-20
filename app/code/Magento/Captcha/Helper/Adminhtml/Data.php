<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Captcha helper for adminhtml area
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Helper\Adminhtml;

class Data extends \Magento\Captcha\Helper\Data
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_backend_config;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Captcha\Model\CaptchaFactory $factory
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Store\Model\Store_Manager $store_manager, \Magento\Framework\Filesystem $filesystem, \Magento\Captcha\Model\Captcha_Factory $factory, \Magento\Backend\App\Config_Interface $backend_config)
    {
        $this->_backend_config = $backend_config;
        parent::__construct($context, $store_manager, $filesystem, $factory);
    }
    /**
     * Returns config value for admin captcha
     *
     * @param string $key The last part of XML_PATH_$area_CAPTCHA_ constant (case insensitive)
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\App\Config\Element
     */
    public function get_config($key, $store = null)
    {
        return $this->_backend_config->get_value('admin/captcha/' . $key);
    }
    /**
     * Get website code
     *
     * @param mixed $website
     * @return string
     */
    protected function _get_website_code($website = null)
    {
        return 'admin';
    }
}