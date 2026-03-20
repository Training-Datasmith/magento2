<?php

declare (strict_types=1);
/**
 * Authorization interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

/**
 * @api
 * @since 100.0.2
 */
interface Authorization_Interface
{
    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function is_allowed($resource, $privilege = null);
}