<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Profiler\Output;

use Magento\Application_Performance_Monitor\Profiler\Metric;
use Magento\Application_Performance_Monitor\Profiler\Metric_Type;
use Magento\Application_Performance_Monitor\Profiler\Output_Interface;
use Magento\Framework\App\Deployment_Config;
use Psr\Log\Logger_Interface;
/**
 * Outputs the performance metrics and other information to Logger
 */
class Logger_Output implements Output_Interface
{
    public const CONFIG_ENABLE_KEY = 'application/performance_monitor/logger_output_enable';
    public const CONFIG_VERBOSE_KEY = 'application/performance_monitor/logger_output_verbose';
    public function __construct(private readonly Logger_Interface $logger, private readonly Deployment_Config $deployment_config)
    {
    }
    /**
     * @inheritDoc
     */
    public function is_enabled(): bool
    {
        return match ($this->deployment_config->get(static::CONFIG_ENABLE_KEY)) {
            1, '1', 'true', true => true,
            default => false,
        };
    }
    /**
     * @inheritDoc
     */
    public function do_output(array $metrics, array $information): void
    {
        if (!$this->is_enabled()) {
            return;
        }
        if (!empty($information['subject'])) {
            $subject = __('Profile information for %1', $information['subject']);
            unset($information['subject']);
        } else {
            $subject = __('Profile information');
        }
        if (!empty($information['requestContentLength'])) {
            $information['requestContentLength'] = $this->pretty_memory_size($information['requestContentLength']);
        }
        $verbose = $this->is_verbose();
        $pretty_metrics = $this->do_output_metrics($metrics, $verbose);
        $message = sprintf("\"%s\": {\n", $subject);
        foreach ($information as $key => $value) {
            $message .= sprintf("\t\"%s\":\t\"%s\",\n", (string) $key, (string) $value);
        }
        foreach ($pretty_metrics as $key => $value) {
            $message .= sprintf("\t\"%s\":\t\"%s\",\n", (string) $key, (string) $value);
        }
        $message = \rtrim($message, ",\n");
        $message .= sprintf("\n}\n");
        $this->logger->debug($message);
    }
    /**
     * Make the metrics pretty and checks verbosity
     */
    private function do_output_metrics(array $metrics, bool $verbose): array
    {
        $pretty_metrics = [];
        /** @var Metric $metric */
        foreach ($metrics as $metric) {
            if (!$verbose && $metric->is_verbose()) {
                continue;
            }
            $pretty_metrics[$metric->get_name()] = match ($metric->get_type()) {
                Metric_Type::SECONDS_ELAPSED_FLOAT => $this->pretty_elapsed_time($metric->get_value()),
                Metric_Type::UNIX_TIMESTAMP_FLOAT => $this->pretty_unix_time($metric->get_value()),
                Metric_Type::MEMORY_SIZE_INT => $this->pretty_memory_size($metric->get_value()),
                default => $metric->get_value(),
            };
        }
        return $pretty_metrics;
    }
    /**
     * Returns a string format of memory with units.
     */
    private function pretty_memory_size(int $size): string
    {
        if (!$this->is_verbose()) {
            $abs_size = abs($size);
            if ($abs_size > 1000000000) {
                return sprintf('%.3g GB', $size / 1000000000.0);
            }
            if ($abs_size > 1000000) {
                return sprintf('%.3g MB', $size / 1000000.0);
            }
            if ($abs_size > 1000) {
                return sprintf('%.3g KB', $size / 1000.0);
            }
        }
        return $size . ' B';
    }
    /**
     * Returns a string format of elapsed time with units.
     */
    private function pretty_elapsed_time(float $time): string
    {
        if ($this->is_verbose()) {
            return $time . ' s';
        }
        $time = (int) $time;
        if ($time > 60) {
            return sprintf('%.3g m', $time / 60.0);
        }
        return $time . ' s';
    }
    /**
     * Returns a string format of unix time with units.
     */
    private function pretty_unix_time(float $time): string
    {
        $time_as_string = sprintf('%.1f', $time);
        return \DateTime::create_from_format('U.u', $time_as_string)->format('Y-m-d\TH:i:s.u');
    }
    /**
     * Returns true when verbose is enabled in configuration.
     */
    private function is_verbose(): bool
    {
        return match ($this->deployment_config->get(static::CONFIG_VERBOSE_KEY)) {
            1, '1', 'true', true => true,
            default => false,
        };
    }
}