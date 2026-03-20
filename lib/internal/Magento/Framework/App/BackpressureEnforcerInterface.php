<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\App\Backpressure\Backpressure_Exceeded_Exception;
use Magento\Framework\App\Backpressure\Context_Interface;
/**
 * Enforces certain backpressure
 */
interface Backpressure_Enforcer_Interface
{
    /**
     * Enforce the backpressure by throwing the exception when limit exceeded
     *
     * @param ContextInterface $context
     * @throws BackpressureExceededException
     * @return void
     */
    public function enforce(Context_Interface $context): void;
}