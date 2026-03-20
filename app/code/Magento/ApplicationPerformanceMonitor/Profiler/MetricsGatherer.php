<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Profiler;

/**
 * Gathers metrics.
 */
class Metrics_Gatherer
{
    public function __construct(private readonly Metrics_Factory $metrics_factory)
    {
    }
    /**
     * Updates the state of this object to the current performance metrics that we measure.
     *
     * @return Metrics
     */
    public function gather_metrics()
    {
        return $this->metrics_factory->create(['memoryUsage' => \memory_get_usage(), 'peakMemoryUsage' => \memory_get_peak_usage(), 'rusage' => \getrusage(), 'microtime' => \microtime(true)]);
    }
}