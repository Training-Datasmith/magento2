<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl\Role;

use Laminas\Permissions\Acl\Exception\InvalidArgumentException;
use Laminas\Permissions\Acl\Role\Role_Interface;
/**
 * Acl role registry. Contains list of roles and their relations.
 */
class Registry extends \Laminas\Permissions\Acl\Role\Registry
{
    /**
     * Add parent to the $role node
     *
     * @param RoleInterface|string $role
     * @param array|RoleInterface|string $parents
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function add_parent($role, $parents)
    {
        try {
            if ($role instanceof Role_Interface) {
                $role_id = $role->get_role_id();
            } else {
                $role_id = $role;
                $role = $this->get($role);
            }
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Child Role id '{$role_id}' does not exist");
        }
        if (!is_array($parents)) {
            $parents = [$parents];
        }
        foreach ($parents as $parent) {
            try {
                if ($parent instanceof Role_Interface) {
                    $role_parent_id = $parent->get_role_id();
                } else {
                    $role_parent_id = $parent;
                }
                $role_parent = $this->get($role_parent_id);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("Parent Role id '{$role_parent_id}' does not exist");
            }
            $this->roles[$role_id]['parents'][$role_parent_id] = $role_parent;
            $this->roles[$role_parent_id]['children'][$role_id] = $role;
        }
        return $this;
    }
}