<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model;

/**
 * Interface for current user identification.
 *
 * @api
 * @since 100.0.2
 */
interface User_Context_Interface
{
    /**#@+
     * User type
     */
    public const USER_TYPE_INTEGRATION = 1;
    public const USER_TYPE_ADMIN = 2;
    public const USER_TYPE_CUSTOMER = 3;
    public const USER_TYPE_GUEST = 4;
    /**#@-*/
    /**
     * Identify current user ID.
     *
     * @return int|null
     */
    public function get_user_id();
    /**
     * Retrieve current user type.
     *
     * @return int|null
     */
    public function get_user_type();
}