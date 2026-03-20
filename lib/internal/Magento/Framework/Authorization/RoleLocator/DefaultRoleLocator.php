<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Authorization\Role_Locator;

class Default_Role_Locator implements \Magento\Framework\Authorization\Role_Locator_Interface
{
    /**
     * Retrieve current role
     *
     * @return string
     */
    public function get_acl_role_id()
    {
        return '';
    }
}