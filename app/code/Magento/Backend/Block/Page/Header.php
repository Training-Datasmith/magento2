<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Page;

/**
 * Adminhtml header block
 *
 * @api
 * @since 100.0.2
 */
class Header extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::page/header.phtml';
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backend_data = null;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_auth_session;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Backend\Helper\Data $backendData
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Model\Auth\Session $auth_session, \Magento\Backend\Helper\Data $backend_data, array $data = [])
    {
        $this->_backend_data = $backend_data;
        $this->_auth_session = $auth_session;
        parent::__construct($context, $data);
    }
    /**
     * Return URL to homepage
     *
     * @return string
     */
    public function get_home_link()
    {
        return $this->_backend_data->get_home_page_url();
    }
    /**
     * Return the current user
     *
     * @return \Magento\User\Model\User|null
     */
    public function get_user()
    {
        return $this->_auth_session->get_user();
    }
    /**
     * Return URL to log out from admin
     *
     * @return string
     */
    public function get_logout_link()
    {
        return $this->get_url('adminhtml/auth/logout');
    }
    /**
     * Check if noscript notice should be displayed
     *
     * @return boolean
     */
    public function display_noscript_notice()
    {
        return $this->_scope_config->get_value('web/browser_capabilities/javascript', \Magento\Store\Model\Scope_Interface::SCOPE_STORE);
    }
}