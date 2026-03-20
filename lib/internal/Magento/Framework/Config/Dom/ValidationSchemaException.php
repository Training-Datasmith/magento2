<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
/**
 * Exception that should be thrown by DOM model when incoming xsd is not valid.
 */
namespace Magento\Framework\Config\Dom;

use Magento\Framework\Exception\Localized_Exception;
/**
 * @api
 * @since 101.0.0
 */
class Validation_Schema_Exception extends Localized_Exception
{
}