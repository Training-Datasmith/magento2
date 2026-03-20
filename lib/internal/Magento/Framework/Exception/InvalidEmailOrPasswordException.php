<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Exception;

/**
 * @api
 * @since 100.0.2
 */
class Invalid_Email_Or_Password_Exception extends Authentication_Exception
{
    /**
     * @deprecated
     */
    public const INVALID_EMAIL_OR_PASSWORD = 'Invalid email or password';
}