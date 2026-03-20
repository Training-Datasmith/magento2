<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl;

/**
 * Access Control List loader
 *
 * All classes implementing this interface should have ability to populate ACL object
 * with data (roles/rules/resources) persisted in external storage.
 *
 * @api
 * @since 100.0.2
 */
interface Loader_Interface
{
    /**
     * Populate ACL with data from external storage
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     * @abstract
     */
    public function populate_acl(\Magento\Framework\Acl $acl);
}