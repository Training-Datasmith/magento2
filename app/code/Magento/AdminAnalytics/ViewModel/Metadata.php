<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Admin_Analytics\View_Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Csp\Helper\Csp_Nonce_Provider;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\Product_Metadata_Interface;
use Magento\Framework\App\State;
use Magento\Framework\View\Element\Block\Argument_Interface;
use Magento\Store\Model\Information;
/**
 * Gets user version and mode
 */
class Metadata implements Argument_Interface
{
    /**
     * @var string
     */
    private $nonce;
    /**
     * @var CspNonceProvider
     */
    private $nonce_provider;
    public function __construct(private readonly Product_Metadata_Interface $product_metadata, private readonly Session $auth_session, private readonly State $app_state, private readonly Scope_Config_Interface $config, ?Csp_Nonce_Provider $nonce_provider = null)
    {
        $this->nonce_provider = $nonce_provider ?: Object_Manager::get_instance()->get(Csp_Nonce_Provider::class);
        $this->nonce = $this->nonce_provider->generate_nonce();
    }
    /**
     * Get product version
     */
    public function get_magento_version(): string
    {
        return $this->product_metadata->get_version();
    }
    /**
     * Get product edition
     */
    public function get_product_edition(): string
    {
        return $this->product_metadata->get_edition();
    }
    /**
     * Get current user id (hash generated from email)
     */
    public function get_current_user(): string
    {
        return hash('sha256', 'ADMIN_USER' . $this->auth_session->get_user()->get_email());
    }
    /**
     * Get Magento mode that the user is using
     */
    public function get_mode(): string
    {
        return $this->app_state->get_mode();
    }
    /**
     * Get created date for current user
     */
    public function get_current_user_created_date(): string
    {
        return $this->auth_session->get_user()->get_created();
    }
    /**
     * Get log date for current user
     */
    public function get_current_user_log_date(): ?string
    {
        return $this->auth_session->get_user()->get_logdate();
    }
    /**
     * Get secure base URL
     */
    public function get_secure_base_url_for_scope(string $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, ?string $scope_code = null): ?string
    {
        return $this->config->get_value(Custom::XML_PATH_SECURE_BASE_URL, $scope, $scope_code);
    }
    /**
     * Get store name
     */
    public function get_store_name_for_scope(string $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, ?string $scope_code = null): ?string
    {
        return $this->config->get_value(Information::XML_PATH_STORE_INFO_NAME, $scope, $scope_code);
    }
    /**
     * Get current user role name
     */
    public function get_current_user_role_name(): string
    {
        return $this->auth_session->get_user()->get_role()->get_role_name();
    }
    /**
     * Get a random nonce for each request.
     */
    public function get_nonce(): string
    {
        return $this->nonce;
    }
}