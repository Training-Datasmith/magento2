<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Authorization;

/**
 * Links Authorization component with application.
 * Responsible for providing the identifier of currently logged in role to \Magento\Framework\Authorization component.
 * Should be implemented by application developer that uses \Magento\Framework\Authorization component.
 *
 * @api
 * @since 100.0.2
 */
interface Role_Locator_Interface
{
    /**
     * Retrieve current role
     *
     * @return string|null
     */
    public function get_acl_role_id();
}