<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor_New_Relic\Profiler\Output;

use Magento\Application_Performance_Monitor\Profiler\Output_Interface;
use Magento\Framework\App\Deployment_Config;
use Magento\New_Relic_Reporting\Model\New_Relic_Wrapper;
/**
 * Outputs the performance metrics and other information to New Relic
 */
class New_Relic_Output implements Output_Interface
{
    public const CONFIG_ENABLE_KEY = 'application/performance_monitor/newrelic_output_enable';
    public const CONFIG_VERBOSE_KEY = 'application/performance_monitor/newrelic_output_verbose';
    public function __construct(private readonly Deployment_Config $deployment_config, private readonly New_Relic_Wrapper $new_relic_wrapper)
    {
    }
    /**
     * @inheritDoc
     */
    public function is_enabled(): bool
    {
        if (!$this->new_relic_wrapper->is_extension_installed()) {
            return false;
        }
        return match ($this->deployment_config->get(static::CONFIG_ENABLE_KEY)) {
            0, '0', 'false', false => false,
            default => true,
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
        foreach ($information as $key => $value) {
            $this->new_relic_wrapper->add_custom_parameter($key, $value);
        }
        $verbose = $this->is_verbose();
        foreach ($metrics as $metric) {
            if (!$verbose && $metric->is_verbose()) {
                continue;
            }
            $this->new_relic_wrapper->add_custom_parameter($metric->get_name(), $metric->get_value());
        }
    }
    /**
     * Is configured to output verbose
     */
    private function is_verbose(): bool
    {
        return match ($this->deployment_config->get(static::CONFIG_VERBOSE_KEY)) {
            1, '1', 'true', true => true,
            default => false,
        };
    }
}