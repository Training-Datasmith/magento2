<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Helper;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem;
/**
 * Captcha image model
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\Abstract_Helper
{
    /**
     * Used for "name" attribute of captcha's input field
     */
    public const INPUT_NAME_FIELD_VALUE = 'captcha';
    /**
     * Always show captcha
     */
    public const MODE_ALWAYS = 'always';
    /**
     * Show captcha only after certain number of unsuccessful attempts
     */
    public const MODE_AFTER_FAIL = 'after_fail';
    /**
     * Captcha fonts path
     */
    public const XML_PATH_CAPTCHA_FONTS = 'captcha/fonts';
    /**
     * Default captcha type
     */
    public const DEFAULT_CAPTCHA_TYPE = 'Zend';
    /**
     * List uses Models of Captcha
     * @var array
     */
    protected $_captcha = [];
    /**
     * @var Filesystem
     */
    protected $_filesystem;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_store_manager;
    /**
     * @var \Magento\Captcha\Model\CaptchaFactory
     */
    protected $_factory;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param \Magento\Captcha\Model\CaptchaFactory $factory
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Store\Model\Store_Manager_Interface $store_manager, Filesystem $filesystem, \Magento\Captcha\Model\Captcha_Factory $factory)
    {
        $this->_store_manager = $store_manager;
        $this->_filesystem = $filesystem;
        $this->_factory = $factory;
        parent::__construct($context);
    }
    /**
     * Get Captcha
     *
     * @param string $formId
     * @return \Magento\Captcha\Model\CaptchaInterface
     */
    public function get_captcha($form_id)
    {
        if (!array_key_exists($form_id, $this->_captcha)) {
            $captcha_type = ucfirst($this->get_config('type'));
            if (!$captcha_type) {
                $captcha_type = self::DEFAULT_CAPTCHA_TYPE;
            } elseif ($captcha_type == 'Default') {
                $captcha_type = $captcha_type . 'Model';
            }
            $this->_captcha[$form_id] = $this->_factory->create($captcha_type, $form_id);
        }
        return $this->_captcha[$form_id];
    }
    /**
     * Returns config value
     *
     * @param string $key The last part of XML_PATH_$area_CAPTCHA_ constant (case insensitive)
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\App\Config\Element
     */
    public function get_config($key, $store = null)
    {
        return $this->scope_config->get_value('customer/captcha/' . $key, \Magento\Store\Model\Scope_Interface::SCOPE_STORE, $store);
    }
    /**
     * Get list of available fonts.
     *
     * Return format:
     * [['arial'] => ['label' => 'Arial', 'path' => '/www/magento/fonts/arial.ttf']]
     *
     * @return array
     */
    public function get_fonts()
    {
        $fonts_config = $this->scope_config->get_value(\Magento\Captcha\Helper\Data::XML_PATH_CAPTCHA_FONTS, 'default');
        $fonts = [];
        if ($fonts_config) {
            $lib_dir = $this->_filesystem->get_directory_read(Directory_List::LIB_INTERNAL);
            foreach ($fonts_config as $font_name => $font_config) {
                $fonts[$font_name] = ['label' => $font_config['label'], 'path' => $lib_dir->get_absolute_path($font_config['path'])];
            }
        }
        return $fonts;
    }
    /**
     * Get captcha image directory
     *
     * @param mixed $website
     * @return string
     */
    public function get_img_dir($website = null)
    {
        // Captcha images are not re-used and should be stored only locally.
        $media_dir = $this->_filesystem->get_directory_write(Directory_List::MEDIA, Filesystem\Driver_Pool::FILE);
        $captcha_dir = '/captcha/' . $this->_get_website_code($website);
        $media_dir->create($captcha_dir);
        return $media_dir->get_absolute_path($captcha_dir) . '/';
    }
    /**
     * Get website code
     *
     * @param mixed $website
     * @return string
     */
    protected function _get_website_code($website = null)
    {
        return $this->_store_manager->get_website($website)->get_code();
    }
    /**
     * Get captcha image base URL
     *
     * @param mixed $website
     * @return string
     */
    public function get_img_url($website = null)
    {
        return $this->_store_manager->get_store()->get_base_url(Directory_List::MEDIA) . 'captcha' . '/' . $this->_get_website_code($website) . '/';
    }
}