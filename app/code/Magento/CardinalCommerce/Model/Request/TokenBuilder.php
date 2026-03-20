<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Cardinal_Commerce\Model\Request;

use Magento\Cardinal_Commerce\Model\Config;
use Magento\Cardinal_Commerce\Model\Jwt_Management;
use Magento\Checkout\Model\Session;
use Magento\Framework\Data_Object\Identity_Generator_Interface;
use Magento\Framework\Intl\Date_Time_Factory;
/**
 * Cardinal request token builder.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Token_Builder
{
    /**
     * @var JwtManagement
     */
    private $jwt_management;
    /**
     * @var Session
     */
    private $checkout_session;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var IdentityGeneratorInterface
     */
    private $identity_generator;
    /**
     * @var DateTimeFactory
     */
    private $date_time_factory;
    /**
     * @param JwtManagement $jwtManagement
     * @param Session $checkoutSession
     * @param Config $config
     * @param IdentityGeneratorInterface $identityGenerator
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(Jwt_Management $jwt_management, Session $checkout_session, Config $config, Identity_Generator_Interface $identity_generator, Date_Time_Factory $date_time_factory)
    {
        $this->jwt_management = $jwt_management;
        $this->checkout_session = $checkout_session;
        $this->config = $config;
        $this->identity_generator = $identity_generator;
        $this->date_time_factory = $date_time_factory;
    }
    /**
     * Builds request JWT.
     *
     * @return string
     */
    public function build()
    {
        $quote = $this->checkout_session->get_quote();
        $current_date = $this->date_time_factory->create('now', new \DateTimeZone('UTC'));
        $order_details = ['OrderDetails' => ['OrderNumber' => $quote->get_id(), 'Amount' => $quote->get_base_grand_total() * 100, 'CurrencyCode' => $quote->get_base_currency_code()]];
        $token = ['jti' => $this->identity_generator->generate_id(), 'iss' => $this->config->get_api_identifier(), 'iat' => $current_date->get_timestamp(), 'OrgUnitId' => $this->config->get_org_unit_id(), 'Payload' => $order_details, 'ObjectifyPayload' => true];
        $jwt = $this->jwt_management->encode($token, $this->config->get_api_key());
        return $jwt;
    }
}