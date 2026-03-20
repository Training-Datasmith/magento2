<?php

/**
 * Copyright 2025 Adobe.
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Captcha\Block;

use Magento\Captcha\Helper\Data as HelperCaptcha;
use Magento\Checkout\Block\Checkout\Layout_Processor_Interface;
class Checkout_Layout_Processor implements Layout_Processor_Interface
{
    /**
     * @param HelperCaptcha $helper
     */
    public function __construct(private readonly Helper_Captcha $helper)
    {
    }
    /**
     * Remove captcha from checkout page if it is disabled
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($js_layout): array
    {
        if ($this->helper->get_config('enable')) {
            $captcha = ['component' => 'Magento_Captcha/js/view/checkout/loginCaptcha', 'displayArea' => 'additional-login-form-fields', 'formId' => 'user_login', 'configSource' => 'checkoutConfig'];
            $js_layout['components']['checkout']['children']['authentication']['children']['captcha'] = $captcha;
            $js_layout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['customer-email']['children']['additional-login-form-fields']['children']['captcha'] = $captcha;
        }
        return $js_layout;
    }
}