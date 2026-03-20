<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Profiler;

use Magento\Framework\App_Interface as Application;
/**
 * Profiles a callable and then outputs it the configured OutputInterface(s).
 */
class Profiler
{
    /**
     * @var Metrics|null used for comparing against previous metrics
     */
    private ?Metrics $previous_after_metrics = null;
    /**
     * @var int used for keeping track of how many requests were already processed by this thread
     */
    private int $previous_request_count = 0;
    /**
     * @param OutputInterface[] $outputs
     * @param InputInterface[] $inputs
     */
    public function __construct(private readonly array $outputs, private readonly array $inputs, private readonly Metrics_Comparator $metrics_comparator, private readonly Metrics_Gatherer $metrics_gatherer)
    {
    }
    /**
     * Does the actual profiling of the function being profiled and then sends results to the outputs.
     */
    public function do_profile(callable $function_being_profiled, Application $application): void
    {
        $previous_after_metrics = $this->previous_after_metrics;
        $previous_request_count = $this->previous_request_count;
        $this->previous_request_count++;
        $this->previous_after_metrics = null;
        if (!$this->is_enabled()) {
            $function_being_profiled();
            return;
        }
        $before_metrics = $this->metrics_gatherer->gather_metrics();
        $function_being_profiled();
        $after_metrics = $this->metrics_gatherer->gather_metrics();
        $this->previous_after_metrics = $after_metrics;
        $information = [];
        foreach ($this->inputs as $input) {
            $information[] = $input->do_input($application);
        }
        $information = array_merge(...$information);
        $information['threadPreviousRequestCount'] = $previous_request_count;
        $this->do_output($before_metrics, $after_metrics, $previous_after_metrics, $information);
    }
    /**
     * Outputs the results of profiling to all enabled outputs.
     *
     * @param array $information extra information that we send to output
     */
    private function do_output(Metrics $before_metrics, Metrics $after_metrics, ?Metrics $previous_after_metrics, array $information): void
    {
        if (!$this->is_enabled()) {
            return;
        }
        $metrics = $this->metrics_comparator->compare_metrics($before_metrics, $after_metrics, $previous_after_metrics);
        foreach ($this->outputs as $output) {
            $output->do_output($metrics, $information);
        }
    }
    /**
     * Returns true if any of our outputs are enabled.
     */
    public function is_enabled(): bool
    {
        foreach ($this->outputs as $output) {
            if ($output->is_enabled()) {
                return true;
            }
        }
        return false;
    }
}