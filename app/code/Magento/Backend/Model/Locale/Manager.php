<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Locale;

/**
 * Locale manager model
 *
 * @api
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 100.0.2
 */
class Manager
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_auth_session;
    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translator;
    /**
     * @var \Magento\Backend\App\ConfigInterface
     * @since 100.1.0
     */
    protected $_backend_config;
    /**
     * Constructor
     *
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\TranslateInterface $translator
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     */
    public function __construct(\Magento\Backend\Model\Session $session, \Magento\Backend\Model\Auth\Session $auth_session, \Magento\Framework\Translate_Interface $translator, \Magento\Backend\App\Config_Interface $backend_config)
    {
        $this->_session = $session;
        $this->_auth_session = $auth_session;
        $this->_translator = $translator;
        $this->_backend_config = $backend_config;
    }
    /**
     * Switch backend locale according to locale code
     *
     * @param string $localeCode
     * @return $this
     */
    public function switch_backend_interface_locale($locale_code)
    {
        $this->_session->set_session_locale(null);
        $this->_auth_session->get_user()->set_interface_locale($locale_code);
        $this->_translator->set_locale($locale_code)->load_data(null, true);
        return $this;
    }
    /**
     * Get general interface locale
     *
     * @return string
     * @since 100.1.0
     */
    public function get_general_locale()
    {
        return $this->_backend_config->get_value('general/locale/code');
    }
    /**
     * Get user interface locale stored in session data
     *
     * @return string
     */
    public function get_user_interface_locale()
    {
        $user_data = $this->_auth_session->get_user();
        $interface_locale = \Magento\Framework\Locale\Resolver::DEFAULT_LOCALE;
        if ($user_data && $user_data->get_interface_locale()) {
            $interface_locale = $user_data->get_interface_locale();
        } elseif ($this->get_general_locale()) {
            $interface_locale = $this->get_general_locale();
        }
        return $interface_locale;
    }
}