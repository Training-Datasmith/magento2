<?php

/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Page_Cache;

use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Stdlib\Cookie\Cookie_Metadata_Factory;
use Magento\Framework\Stdlib\Cookie_Manager_Interface;
/**
 * PageCache Version
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Version
{
    /**
     * Name of cookie that holds private content version
     */
    public const COOKIE_NAME = 'private_content_version';
    /**
     * Ten years cookie period
     */
    public const COOKIE_PERIOD = 315360000;
    /**
     * Config setting for disabling session for GraphQl
     */
    private const XML_PATH_GRAPHQL_DISABLE_SESSION = 'graphql/session/disable';
    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(protected readonly Cookie_Manager_Interface $cookie_manager, protected readonly Cookie_Metadata_Factory $cookie_metadata_factory, protected readonly Http $request, protected readonly Scope_Config_Interface $scope_config)
    {
    }
    /**
     * Generate unique version identifier
     *
     * @return string
     */
    protected function generate_value(): string
    {
        //phpcs:ignore
        return md5(rand() . time());
    }
    /**
     * Handle private content version cookie
     * Set cookie if it is not set.
     * Increment version on post requests.
     * In all other cases do nothing.
     *
     * @return void
     */
    public function process(): void
    {
        if (!$this->request->is_post()) {
            return;
        }
        $original_path_info = $this->request->get_original_path_info();
        if ($original_path_info && str_contains($original_path_info, '/graphql') && $this->is_session_disabled() === true) {
            return;
        }
        $public_cookie_metadata = $this->cookie_metadata_factory->create_public_cookie_metadata()->set_duration(self::COOKIE_PERIOD)->set_path('/')->set_secure($this->request->is_secure())->set_http_only(false)->set_same_site('Lax');
        $this->cookie_manager->set_public_cookie(self::COOKIE_NAME, $this->generate_value(), $public_cookie_metadata);
    }
    /**
     * Returns configuration setting for disable session for GraphQl
     *
     * @return bool
     */
    private function is_session_disabled(): bool
    {
        return (bool) $this->scope_config->get_value(self::XML_PATH_GRAPHQL_DISABLE_SESSION, Scope_Config_Interface::SCOPE_TYPE_DEFAULT);
    }
}