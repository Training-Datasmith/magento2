<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Authorization;

/**
 * Responsible for internal authorization decision making based on provided role, resource and privilege
 *
 * @api
 * @since 100.0.2
 */
interface Policy_Interface
{
    /**
     * Check whether given role has access to given resource
     *
     * @abstract
     * @param string $roleId
     * @param string $resourceId
     * @param string|null $privilege
     * @return bool
     */
    public function is_allowed($role_id, $resource_id, $privilege = null);
}