<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Locale;

/**
 * Backend locale model
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Resolver extends \Magento\Framework\Locale\Resolver
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;
    /**
     * @var \Magento\Backend\Model\Locale\Manager
     */
    protected $_locale_manager;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Framework\Validator\Locale
     */
    protected $_locale_validator;
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $defaultLocalePath
     * @param string $scopeType
     * @param \Magento\Backend\Model\Session $session
     * @param Manager $localeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Validator\Locale $localeValidator
     * @param string|null $locale
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\App\Config\Scope_Config_Interface $scope_config, $default_locale_path, $scope_type, \Magento\Backend\Model\Session $session, \Magento\Backend\Model\Locale\Manager $locale_manager, \Magento\Framework\App\Request_Interface $request, \Magento\Framework\Validator\Locale $locale_validator, $locale = null)
    {
        $this->_session = $session;
        $this->_locale_manager = $locale_manager;
        $this->_request = $request;
        $this->_locale_validator = $locale_validator;
        parent::__construct($scope_config, $default_locale_path, $scope_type, $locale);
    }
    /**
     * Set locale
     *
     * @param string $locale
     * @return $this
     */
    public function set_locale($locale = null)
    {
        $force_locale = $this->_request->get_param('locale', null);
        if (!$this->_locale_validator->is_valid($force_locale)) {
            $force_locale = false;
        }
        $session_locale = $this->_session->get_session_locale();
        $user_locale = $this->_locale_manager->get_user_interface_locale();
        $locale_codes = array_filter([$force_locale, $locale, $session_locale, $user_locale]);
        if (count($locale_codes)) {
            $locale = reset($locale_codes);
        }
        return parent::set_locale($locale);
    }
}