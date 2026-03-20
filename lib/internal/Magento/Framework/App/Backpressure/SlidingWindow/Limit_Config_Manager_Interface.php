<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure\Sliding_Window;

use Magento\Framework\App\Backpressure\Context_Interface;
/**
 * Provides limit configuration for request contexts
 */
interface Limit_Config_Manager_Interface
{
    /**
     * Find limits for given context
     *
     * @param ContextInterface $context
     * @return LimitConfig
     */
    public function read_limit(Context_Interface $context): Limit_Config;
}