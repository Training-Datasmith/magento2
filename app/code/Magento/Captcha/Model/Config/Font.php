<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Captcha image model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Model\Config;

class Font implements \Magento\Framework\Option\Array_Interface
{
    /**
     * Captcha data
     *
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_captcha_data = null;
    /**
     * @param \Magento\Captcha\Helper\Data $captchaData
     */
    public function __construct(\Magento\Captcha\Helper\Data $captcha_data)
    {
        $this->_captcha_data = $captcha_data;
    }
    /**
     * Get options for font selection field
     *
     * @return array
     */
    public function to_option_array()
    {
        $option_array = [];
        foreach ($this->_captcha_data->get_fonts() as $font_name => $font_data) {
            $option_array[] = ['label' => $font_data['label'], 'value' => $font_name];
        }
        return $option_array;
    }
}