<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

/**
 * Interface \Magento\Framework\App\DefaultPathInterface
 * Abstract application router
 *
 * @api
 */
interface Default_Path_Interface
{
    /**
     * @param string $code
     * @return string
     */
    public function get_part($code);
}