<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Connector;

use Laminas\Http\Request;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Representation of an 'OTP' request.
 *
 * The request is responsible for obtaining of an OTP from the MBI service.
 *
 * OTP (One-Time Password) is a password that is valid for short period of time
 * and may be used only for one login session.
 */
class OTPRequest
{
    /**
     * Path to the configuration value which contains
     * an URL that provides an OTP.
     */
    private string $otpUrlConfigPath = 'analytics/url/otp';

    public function __construct(
        /**
         * Resource for handling MBI token value.
         */
        private readonly AnalyticsToken $analyticsToken,
        private readonly Http\ClientInterface $httpClient,
        private readonly ScopeConfigInterface $config,
        private readonly ResponseResolver $responseResolver,
        private readonly LoggerInterface $logger
    ) {
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

        if ($this->analyticsToken->isTokenExist()) {
            $response = $this->httpClient->request(
                Request::METHOD_POST,
                $this->config->getValue($this->otpUrlConfigPath),
                [
                    'access-token' => $this->analyticsToken->getToken(),
                    'url' => $this->config->getValue(Store::XML_PATH_SECURE_BASE_URL),
                ]
            );

            $result = $this->responseResolver->getResult($response);
            if (!$result) {
                $this->logger->warning(
                    sprintf(
                        'Obtaining of an OTP from the MBI service has been failed: %s. Content-Type: %s',
                        !empty($response->getBody()) ? $response->getBody() : 'Response body is empty',
                        $response->getHeaders()->has('Content-Type') ?
                            $response->getHeaders()->get('Content-Type')->getFieldValue() :
                            ''
                    )
                );
            }
        }

        return $result;
    }
}
