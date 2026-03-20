<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Block\Captcha;

/**
 * Captcha block
 */
class Default_Captcha extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Captcha::default.phtml';
    /**
     * @var string
     */
    protected $_captcha;
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_captcha_data;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Captcha\Helper\Data $captcha_data, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_captcha_data = $captcha_data;
    }
    /**
     * Returns template path
     *
     * @return string
     */
    public function get_template()
    {
        return $this->get_is_ajax() ? '' : $this->_template;
    }
    /**
     * Returns URL to controller action which returns new captcha image
     *
     * @return string
     */
    public function get_refresh_url()
    {
        $store = $this->_store_manager->get_store();
        return $store->get_url('captcha/refresh', ['_secure' => $store->is_currently_secure()]);
    }
    /**
     * Renders captcha HTML (if required)
     *
     * @return string
     */
    protected function _to_html()
    {
        if ($this->get_captcha_model()->is_required()) {
            $this->get_captcha_model()->generate();
            return parent::_to_html();
        }
        return '';
    }
    /**
     * Returns captcha model
     *
     * @return \Magento\Captcha\Model\CaptchaInterface
     */
    public function get_captcha_model()
    {
        return $this->_captcha_data->get_captcha($this->get_form_id());
    }
}