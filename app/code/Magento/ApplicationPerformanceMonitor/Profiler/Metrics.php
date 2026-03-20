<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Profiler;

/**
 * Gathers and stores metrics. Compares against another one to get the deltas.
 */
class Metrics
{
    public function __construct(private readonly int $peak_memory_usage, private readonly int $memory_usage, private readonly array $rusage, private readonly float $microtime)
    {
    }
    /**
     * Gets peak memory usage
     */
    public function get_peak_memory_usage(): int
    {
        return $this->peak_memory_usage;
    }
    /**
     * Gets memory usage
     */
    public function get_memory_usage(): int
    {
        return $this->memory_usage;
    }
    /**
     * Gets fusage
     */
    public function get_rusage(): array
    {
        return $this->rusage;
    }
    /**
     * Gets microtime
     */
    public function get_microtime(): float
    {
        return $this->microtime;
    }
}