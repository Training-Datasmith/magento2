<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Captcha\Observer;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request_Interface;
/**
 * Extract given captcha word.
 */
class Captcha_String_Resolver
{
    /**
     * Get Captcha String
     *
     * @param \Magento\Framework\App\RequestInterface|HttpRequest $request
     * @param string $formId
     * @return string
     */
    public function resolve(Request_Interface $request, $form_id)
    {
        $value = '';
        $captcha_params = $request->get_post(Captcha_Helper::INPUT_NAME_FIELD_VALUE);
        if (!empty($captcha_params) && !empty($captcha_params[$form_id])) {
            $value = $captcha_params[$form_id];
        } elseif ($header_value = $request->get_header('X-Captcha')) {
            //CAPTCHA was provided via header for this XHR/web API request.
            $value = $header_value;
        }
        return $value;
    }
}