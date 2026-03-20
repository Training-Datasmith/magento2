<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Profiler;

/**
 * Compares metrics against another one to get the deltas.
 */
class Metrics_Comparator
{
    public function __construct(private readonly Metric_Factory $metric_factory)
    {
    }
    /**
     * Compares with a previous Metrics and returns results as array.
     *
     * @return Metric[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function compare_metrics(Metrics $before_metrics, Metrics $after_metrics, ?Metrics $previous_after_metrics): array
    {
        $metrics = [];
        $metrics['memoryUsageBefore'] = $this->metric_factory->create(['type' => Metric_Type::MEMORY_SIZE_INT, 'name' => 'memoryUsageBefore', 'value' => $before_metrics->get_memory_usage(), 'verbose' => true]);
        $metrics['memoryUsageAfter'] = $this->metric_factory->create(['type' => Metric_Type::MEMORY_SIZE_INT, 'name' => 'memoryUsageAfter', 'value' => $after_metrics->get_memory_usage(), 'verbose' => false]);
        if ($previous_after_metrics) {
            $metrics['memoryUsageAfterComparedToPrevious'] = $this->metric_factory->create(['type' => Metric_Type::MEMORY_SIZE_INT, 'name' => 'memoryUsageAfterComparedToPrevious', 'value' => $after_metrics->get_memory_usage() - $previous_after_metrics->get_memory_usage(), 'verbose' => false]);
        }
        $metrics['memoryUsageDelta'] = $this->metric_factory->create(['type' => Metric_Type::MEMORY_SIZE_INT, 'name' => 'memoryUsageDelta', 'value' => $after_metrics->get_memory_usage() - $before_metrics->get_memory_usage(), 'verbose' => false]);
        $metrics['peakMemoryUsageBefore'] = $this->metric_factory->create(['type' => Metric_Type::MEMORY_SIZE_INT, 'name' => 'peakMemoryUsageBefore', 'value' => $before_metrics->get_peak_memory_usage(), 'verbose' => true]);
        $metrics['peakMemoryUsageAfter'] = $this->metric_factory->create(['type' => Metric_Type::MEMORY_SIZE_INT, 'name' => 'peakMemoryUsageAfter', 'value' => $after_metrics->get_peak_memory_usage(), 'verbose' => false]);
        $metrics['peakMemoryUsageDelta'] = $this->metric_factory->create(['type' => Metric_Type::MEMORY_SIZE_INT, 'name' => 'peakMemoryUsageDelta', 'value' => $after_metrics->get_peak_memory_usage() - $before_metrics->get_peak_memory_usage(), 'verbose' => false]);
        $metrics['wallTimeBefore'] = $this->metric_factory->create(['type' => Metric_Type::UNIX_TIMESTAMP_FLOAT, 'name' => 'wallTimeBefore', 'value' => $before_metrics->get_microtime(), 'verbose' => true]);
        $metrics['wallTimeAfter'] = $this->metric_factory->create(['type' => Metric_Type::UNIX_TIMESTAMP_FLOAT, 'name' => 'wallTimeAfter', 'value' => $after_metrics->get_microtime(), 'verbose' => true]);
        $metrics['wallTimeElapsed'] = $this->metric_factory->create(['type' => Metric_Type::SECONDS_ELAPSED_FLOAT, 'name' => 'wallTimeElapsed', 'value' => $after_metrics->get_microtime() - $before_metrics->get_microtime(), 'verbose' => false]);
        $metrics['userTimeBefore'] = $this->metric_factory->create(['type' => Metric_Type::SECONDS_ELAPSED_FLOAT, 'name' => 'userTimeBefore', 'value' => $before_metrics->get_rusage()['ru_utime.tv_sec'] + 1.0E-6 * $before_metrics->get_rusage()['ru_utime.tv_usec'], 'verbose' => true]);
        $metrics['userTimeAfter'] = $this->metric_factory->create(['type' => Metric_Type::SECONDS_ELAPSED_FLOAT, 'name' => 'userTimeAfter', 'value' => $after_metrics->get_rusage()['ru_utime.tv_sec'] + 1.0E-6 * $after_metrics->get_rusage()['ru_utime.tv_usec'], 'verbose' => true]);
        $metrics['userTimeElapsed'] = $this->metric_factory->create(['type' => Metric_Type::SECONDS_ELAPSED_FLOAT, 'name' => 'userTimeElapsed', 'value' => $metrics['userTimeAfter']->get_value() - $metrics['userTimeBefore']->get_value(), 'verbose' => true]);
        $metrics['systemTimeBefore'] = $this->metric_factory->create(['type' => Metric_Type::SECONDS_ELAPSED_FLOAT, 'name' => 'systemTimeBefore', 'value' => $before_metrics->get_rusage()['ru_stime.tv_sec'] + 1.0E-6 * $before_metrics->get_rusage()['ru_stime.tv_usec'], 'verbose' => true]);
        $metrics['systemTimeAfter'] = $this->metric_factory->create(['type' => Metric_Type::SECONDS_ELAPSED_FLOAT, 'name' => 'systemTimeAfter', 'value' => $after_metrics->get_rusage()['ru_stime.tv_sec'] + 1.0E-6 * $after_metrics->get_rusage()['ru_stime.tv_usec'], 'verbose' => true]);
        $metrics['systemTimeElapsed'] = $this->metric_factory->create(['type' => Metric_Type::SECONDS_ELAPSED_FLOAT, 'name' => 'systemTimeElapsed', 'value' => $metrics['systemTimeAfter']->get_value() - $metrics['systemTimeBefore']->get_value(), 'verbose' => true]);
        return $metrics;
    }
}