<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Controller;

/**
 * Interface \Magento\Setup\Controller\ResponseTypeInterface
 *
 */
interface ResponseTypeInterface
{
    /**#@+
     * Response Type values
     */
    public const RESPONSE_TYPE_SUCCESS = 'success';
    public const RESPONSE_TYPE_ERROR = 'error';
    /**#@-*/
}
