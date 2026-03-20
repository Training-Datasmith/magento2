<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Exception;

/**
 * @api
 * @since 100.1.0
 */
class Configuration_Mismatch_Exception extends Localized_Exception
{
    /**
     * @deprecated
     */
    public const AUTHENTICATION_ERROR = 'Configuration mismatch detected.';
}