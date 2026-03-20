<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Block\Adminhtml\Captcha;

/**
 * Captcha block for adminhtml area
 */
class Default_Captcha extends \Magento\Captcha\Block\Captcha\Default_Captcha
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Captcha\Helper\Data $captcha_data, \Magento\Backend\Model\Url_Interface $url, \Magento\Backend\App\Config_Interface $config, array $data = [])
    {
        parent::__construct($context, $captcha_data, $data);
        $this->_url = $url;
        $this->_config = $config;
    }
    /**
     * Returns URL to controller action which returns new captcha image
     *
     * @return string
     */
    public function get_refresh_url()
    {
        return $this->_url->get_url('adminhtml/refresh/refresh', ['_secure' => $this->_config->is_set_flag('web/secure/use_in_adminhtml'), '_nosecret' => true]);
    }
}