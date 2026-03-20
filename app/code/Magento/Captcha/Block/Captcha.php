<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Captcha block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Block;

/**
 * @api
 * @since 100.0.2
 */
class Captcha extends \Magento\Framework\View\Element\Template
{
    /**
     * Captcha data
     *
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_captcha_data = null;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Captcha\Helper\Data $captcha_data, array $data = [])
    {
        $this->_captcha_data = $captcha_data;
        parent::__construct($context, $data);
        $this->_is_scope_private = true;
    }
    /**
     * Renders captcha HTML (if required)
     *
     * @return string
     */
    protected function _to_html()
    {
        $block_path = $this->_captcha_data->get_captcha($this->get_form_id())->get_block_name();
        $block = $this->get_layout()->create_block($block_path);
        $block->set_data($this->get_data());
        return $block->to_html();
    }
}