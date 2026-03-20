<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Cardinal_Commerce\Model;

use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Store\Model\Scope_Interface;
/**
 * CardinalCommerce integration configuration.
 *
 * Class is a proxy service for retrieving configuration settings.
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    private $scope_config;
    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Scope_Config_Interface $scope_config)
    {
        $this->scope_config = $scope_config;
    }
    /**
     * Returns CardinalCommerce API Key used for authentication.
     *
     * A shared secret value between the merchant and Cardinal. This value should never be exposed to the public.
     *
     * @param int|null $storeId
     * @return string
     */
    public function get_api_key(?int $store_id = null): string
    {
        $api_key = $this->scope_config->get_value('three_d_secure/cardinal/api_key', Scope_Interface::SCOPE_STORE, $store_id);
        return $api_key;
    }
    /**
     * Returns CardinalCommerce API Identifier.
     *
     * GUID used to identify the specific API Key.
     *
     * @param int|null $storeId
     * @return string
     */
    public function get_api_identifier(?int $store_id = null): string
    {
        $api_identifier = $this->scope_config->get_value('three_d_secure/cardinal/api_identifier', Scope_Interface::SCOPE_STORE, $store_id);
        return $api_identifier;
    }
    /**
     * Returns CardinalCommerce Org Unit Id.
     *
     * GUID to identify the merchant organization within Cardinal systems.
     *
     * @param int|null $storeId
     * @return string
     */
    public function get_org_unit_id(?int $store_id = null): string
    {
        $org_unit_id = $this->scope_config->get_value('three_d_secure/cardinal/org_unit_id', Scope_Interface::SCOPE_STORE, $store_id);
        return $org_unit_id;
    }
    /**
     * Returns CardinalCommerce environment.
     *
     * Sandbox or production.
     *
     * @param int|null $storeId
     * @return string
     */
    public function get_environment(?int $store_id = null): string
    {
        $environment = $this->scope_config->get_value('three_d_secure/cardinal/environment', Scope_Interface::SCOPE_STORE, $store_id);
        return $environment;
    }
    /**
     * If is "true" extra information about interaction with CardinalCommerce API are written to payment.log file
     *
     * @param int|null $storeId
     * @return bool
     */
    public function is_debug_mode_enabled(?int $store_id = null): bool
    {
        $debug_mode_enabled = $this->scope_config->is_set_flag('three_d_secure/cardinal/debug', Scope_Interface::SCOPE_STORE, $store_id);
        return $debug_mode_enabled;
    }
}