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
class Email_Not_Confirmed_Exception extends Authentication_Exception
{
    /**
     * @deprecated
     */
    public const EMAIL_NOT_CONFIRMED = 'Email not confirmed';
}