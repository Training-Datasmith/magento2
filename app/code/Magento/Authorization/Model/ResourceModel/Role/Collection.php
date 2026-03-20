<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model\Resource_Model\Role;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
/**
 * Admin role collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\Resource_Model\Db\Collection\Abstract_Collection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Authorization\Model\Role::class, \Magento\Authorization\Model\Resource_Model\Role::class);
    }
    /**
     * Add user filter
     *
     * @param int $userId
     * @param string $userType
     * @return $this
     */
    public function set_user_filter($user_id, $user_type): static
    {
        $this->add_field_to_filter('user_id', $user_id);
        $this->add_field_to_filter('user_type', $user_type);
        return $this;
    }
    /**
     * Set roles filter
     *
     * @return $this
     */
    public function set_roles_filter(): static
    {
        $this->add_field_to_filter('role_type', Role_Group::ROLE_TYPE);
        return $this;
    }
    /**
     * Convert to option array
     *
     * @return array
     */
    public function to_option_array()
    {
        return $this->_to_option_array('role_id', 'role_name');
    }
}