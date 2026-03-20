<?php

/**
 * Copyright 2025 Adobe.
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Captcha\Block\Customer\Authentication_Popup;

use Magento\Captcha\Helper\Data as HelperCaptcha;
use Magento\Checkout\Block\Checkout\Layout_Processor_Interface;
class Layout_Processor implements Layout_Processor_Interface
{
    /**
     * @param HelperCaptcha $helper
     */
    public function __construct(private readonly Helper_Captcha $helper)
    {
    }
    /**
     * Process jsLayout of checkout page
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($js_layout): array
    {
        if ($this->helper->get_config('enable')) {
            $js_layout['components']['authenticationPopup']['children']['captcha'] = ['component' => 'Magento_Captcha/js/view/checkout/loginCaptcha', 'displayArea' => 'additional-login-form-fields', 'formId' => 'user_login', 'configSource' => 'checkout'];
        }
        return $js_layout;
    }
}