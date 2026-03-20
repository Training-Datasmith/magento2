<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Profiler;

/**
 * Interface for different ways of outputting our performance data.
 */
interface Output_Interface
{
    /**
     * Whether this output is currently enabled
     */
    public function is_enabled(): bool;
    /**
     * Output our metrics and other information
     *
     * @param Metric[] $metrics
     */
    public function do_output(array $metrics, array $information): void;
}