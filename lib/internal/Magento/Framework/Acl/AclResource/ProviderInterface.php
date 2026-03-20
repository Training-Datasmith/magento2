<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl\Acl_Resource;

/**
 * Acl resources provider interface
 *
 * @api
 * @since 100.0.2
 */
interface Provider_Interface
{
    /**
     * Retrieve ACL resources
     *
     * @return array
     */
    public function get_acl_resources();
}