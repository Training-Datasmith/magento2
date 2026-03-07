<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

/**
 * Gathers and stores metrics. Compares against another one to get the deltas.
 */
class Metrics
{
    public function __construct(
        private readonly int $peakMemoryUsage,
        private readonly int $memoryUsage,
        private readonly array $rusage,
        private readonly float $microtime
    ) {
    }

    /**
     * Gets peak memory usage
     */
    public function getPeakMemoryUsage(): int
    {
        return $this->peakMemoryUsage;
    }

    /**
     * Gets memory usage
     */
    public function getMemoryUsage(): int
    {
        return $this->memoryUsage;
    }

    /**
     * Gets fusage
     */
    public function getRusage(): array
    {
        return $this->rusage;
    }

    /**
     * Gets microtime
     */
    public function getMicrotime(): float
    {
        return $this->microtime;
    }
}
