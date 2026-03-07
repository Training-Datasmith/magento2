<?php

declare(strict_types=1);
/**
 * Authorization service exception
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Exception;

/**
 * @api
 * @since 100.0.2
 */
class AuthorizationException extends LocalizedException
{
    /**
     * @deprecated
     */
    public const NOT_AUTHORIZED = "The consumer isn't authorized to access %resources.";
}
