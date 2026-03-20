<?php

/**
 * Copyright 2012 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Authorization\Policy;

use Magento\Framework\Acl\Builder;
use Magento\Framework\Acl\Role\Current_Role_Context;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Authorization\Policy_Interface;
/**
 * Uses ACL to control access. If ACL doesn't contain provided resource, permission for all resources is checked.
 */
class Acl implements Policy_Interface
{
    /**
     * @var Builder
     */
    protected $_acl_builder;
    /**
     * @var CurrentRoleContext
     */
    private $role_context;
    /**
     * @param Builder $aclBuilder
     * @param ?CurrentRoleContext $roleContext
     */
    public function __construct(Builder $acl_builder, ?Current_Role_Context $role_context = null)
    {
        $this->_acl_builder = $acl_builder;
        $this->role_context = $role_context ?? Object_Manager::get_instance()->get(Current_Role_Context::class);
    }
    /**
     * Check whether given role has access to give id
     *
     * @param string $roleId
     * @param string $resourceId
     * @param string $privilege
     * @return bool
     */
    public function is_allowed($role_id, $resource_id, $privilege = null)
    {
        if ($role_id === null || $role_id === '') {
            //no user is logged in
            return false;
        }
        try {
            $this->role_context->set_role_id((int) $role_id);
            return $this->_acl_builder->get_acl()->is_allowed($role_id, $resource_id, $privilege);
        } catch (\Exception $e) {
            try {
                if (!$this->_acl_builder->get_acl()->has_resource($resource_id)) {
                    return $this->_acl_builder->get_acl()->is_allowed($role_id, null, $privilege);
                }
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (\Exception $e) {
            }
        } finally {
            $this->role_context->_reset_state();
        }
        return false;
    }
}