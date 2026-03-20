<?php

declare (strict_types=1);
/**
 * Authentication exception
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Exception;

/**
 * @api
 * @since 100.0.2
 */
class Authentication_Exception extends Localized_Exception
{
    /**
     * @deprecated
     */
    public const AUTHENTICATION_ERROR = 'An authentication error occurred. Verify and try again.';
}