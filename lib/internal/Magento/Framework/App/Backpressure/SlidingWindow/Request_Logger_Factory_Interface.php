<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure\Sliding_Window;

use Magento\Framework\Exception\RuntimeException;
/**
 * Creates Backpressure Logger by type
 */
interface Request_Logger_Factory_Interface
{
    /**
     * Creates Backpressure Logger object by type
     *
     * @param string $type
     * @return RequestLoggerInterface
     * @throws RuntimeException
     */
    public function create(string $type): Request_Logger_Interface;
}