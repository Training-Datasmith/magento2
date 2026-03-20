<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Authorization;

/**
 * @api
 * @since 100.0.2
 */
class Role_Locator implements \Magento\Framework\Authorization\Role_Locator_Interface
{
    /**
     * Authentication service
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;
    /**
     * @param \Magento\Backend\Model\Auth\Session $session
     */
    public function __construct(\Magento\Backend\Model\Auth\Session $session)
    {
        $this->_session = $session;
    }
    /**
     * Retrieve current role
     *
     * @return string|null
     */
    public function get_acl_role_id()
    {
        if ($this->_session->has_user()) {
            return $this->_session->get_user()->get_acl_role();
        }
        return null;
    }
}