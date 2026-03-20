<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector;

use Laminas\Http\Request;
use Magento\Analytics\Model\Analytics_Token;
use Magento\Analytics\Model\Connector\Http\Response_Resolver;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Store\Model\Store;
use Psr\Log\Logger_Interface;
/**
 * Representation of an 'OTP' request.
 *
 * The request is responsible for obtaining of an OTP from the MBI service.
 *
 * OTP (One-Time Password) is a password that is valid for short period of time
 * and may be used only for one login session.
 */
class Otp_Request
{
    /**
     * Path to the configuration value which contains
     * an URL that provides an OTP.
     */
    private string $otp_url_config_path = 'analytics/url/otp';
    public function __construct(
        /**
         * Resource for handling MBI token value.
         */
        private readonly Analytics_Token $analytics_token,
        private readonly Http\Client_Interface $http_client,
        private readonly Scope_Config_Interface $config,
        private readonly Response_Resolver $response_resolver,
        private readonly Logger_Interface $logger
    )
    {
    }
    /**
     * Performs obtaining of an OTP from the MBI service.
     *
     * Returns received OTP or FALSE in case of failure.
     *
     * @return string|false
     */
    public function call()
    {
        $result = false;
        if ($this->analytics_token->is_token_exist()) {
            $response = $this->http_client->request(Request::METHOD_POST, $this->config->get_value($this->otp_url_config_path), ['access-token' => $this->analytics_token->get_token(), 'url' => $this->config->get_value(Store::XML_PATH_SECURE_BASE_URL)]);
            $result = $this->response_resolver->get_result($response);
            if (!$result) {
                $this->logger->warning(sprintf('Obtaining of an OTP from the MBI service has been failed: %s. Content-Type: %s', !empty($response->get_body()) ? $response->get_body() : 'Response body is empty', $response->get_headers()->has('Content-Type') ? $response->get_headers()->get('Content-Type')->get_field_value() : ''));
            }
        }
        return $result;
    }
}