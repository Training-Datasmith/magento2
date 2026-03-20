<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Default authorization policy. Allows access to all resources
 */
namespace Magento\Framework\Authorization\Policy;

class Default_Policy implements \Magento\Framework\Authorization\Policy_Interface
{
    /**
     * Check whether given role has access to give id
     *
     * @param string $roleId
     * @param string $resourceId
     * @param string $privilege
     * @return true
     */
    public function is_allowed($role_id, $resource_id, $privilege = null)
    {
        return true;
    }
}