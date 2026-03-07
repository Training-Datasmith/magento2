<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\DB;

/**
 * Class FieldDataConversionException
 */
class FieldDataConversionException extends \Exception
{
    /**
     * Message pattern for corrupted data exception
     */
    public const MESSAGE_PATTERN = 'Error converting field `%s` in table `%s` where `%s`=%s using %s.'
        . PHP_EOL
        . 'Fix data or replace with a valid value.'
        . PHP_EOL
        . "Failure reason: '%s'";
}
