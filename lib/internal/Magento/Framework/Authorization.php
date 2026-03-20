<?php

declare (strict_types=1);
/**
 * Magento Authorization component. Can be used to add authorization facility to any application
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

class Authorization implements \Magento\Framework\Authorization_Interface
{
    /**
     * ACL policy
     *
     * @var \Magento\Framework\Authorization\PolicyInterface
     */
    protected $_acl_policy;
    /**
     * ACL role locator
     *
     * @var \Magento\Framework\Authorization\RoleLocatorInterface
     */
    protected $_acl_role_locator;
    /**
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param \Magento\Framework\Authorization\RoleLocatorInterface $roleLocator
     */
    public function __construct(\Magento\Framework\Authorization\Policy_Interface $acl_policy, \Magento\Framework\Authorization\Role_Locator_Interface $role_locator)
    {
        $this->_acl_policy = $acl_policy;
        $this->_acl_role_locator = $role_locator;
    }
    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function is_allowed($resource, $privilege = null)
    {
        return $this->_acl_policy->is_allowed($this->_acl_role_locator->get_acl_role_id(), $resource, $privilege);
    }
}