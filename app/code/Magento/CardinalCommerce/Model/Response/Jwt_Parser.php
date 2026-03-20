<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Cardinal_Commerce\Model\Response;

use Magento\Cardinal_Commerce\Model\Config;
use Magento\Cardinal_Commerce\Model\Jwt_Management;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Payment\Model\Method\Logger as PaymentLogger;
use Psr\Log\Logger_Interface;
/**
 * Parses content of CardinalCommerce response JWT.
 */
class Jwt_Parser implements Jwt_Parser_Interface
{
    /**
     * @var JwtManagement
     */
    private $jwt_management;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var JwtPayloadValidatorInterface
     */
    private $token_validator;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var PaymentLogger
     */
    private $payment_logger;
    /**
     * @param JwtManagement $jwtManagement
     * @param Config $config
     * @param JwtPayloadValidatorInterface $tokenValidator
     * @param PaymentLogger $paymentLogger
     * @param LoggerInterface $logger
     */
    public function __construct(Jwt_Management $jwt_management, Config $config, Jwt_Payload_Validator_Interface $token_validator, Payment_Logger $payment_logger, Logger_Interface $logger)
    {
        $this->jwt_management = $jwt_management;
        $this->config = $config;
        $this->token_validator = $token_validator;
        $this->payment_logger = $payment_logger;
        $this->logger = $logger;
    }
    /**
     * Returns response JWT payload.
     *
     * @param string $jwt
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $jwt): array
    {
        $jwt_payload = '';
        try {
            $this->debug(['Cardinal Response JWT:' => $jwt]);
            $jwt_payload = $this->jwt_management->decode($jwt, $this->config->get_api_key());
            $this->debug(['Cardinal Response JWT payload:' => $jwt_payload]);
            if (!$this->token_validator->validate($jwt_payload)) {
                $this->throw_exception();
            }
        } catch (\InvalidArgumentException $e) {
            $this->logger->critical($e, ['CardinalCommerce3DSecure']);
            $this->throw_exception();
        }
        return $jwt_payload;
    }
    /**
     * Log JWT data.
     *
     * @param array $data
     * @return void
     */
    private function debug(array $data)
    {
        if ($this->config->is_debug_mode_enabled()) {
            $this->payment_logger->debug($data, ['iss'], true);
        }
    }
    /**
     * Throw general localized exception.
     *
     * @return void
     * @throws LocalizedException
     */
    private function throw_exception()
    {
        throw new Localized_Exception(__('Authentication Failed. Your card issuer cannot authenticate this card. ' . 'Please select another card or form of payment to complete your purchase.'));
    }
}