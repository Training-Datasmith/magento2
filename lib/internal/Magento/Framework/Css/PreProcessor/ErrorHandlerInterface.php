<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Css\Pre_Processor;

/**
 * Error handler interface
 *
 * @api
 */
interface Error_Handler_Interface
{
    /**
     * Process an exception which was thrown during processing dynamic instructions
     *
     * @param \Exception $e
     * @return void
     */
    public function process_exception(\Exception $e);
}