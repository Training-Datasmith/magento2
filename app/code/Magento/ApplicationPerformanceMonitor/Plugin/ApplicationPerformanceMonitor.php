<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Plugin;

use Magento\Application_Performance_Monitor\Profiler\Profiler;
use Magento\Framework\App\Response_Interface;
use Magento\Framework\App_Interface as Application;
/**
 * Plugin that uses profiler to get performance metrics from Application
 */
class Application_Performance_Monitor
{
    public function __construct(private readonly Profiler $profiler)
    {
    }
    /**
     * Plugin that uses profiler to get performance metrics for application
     */
    public function around_launch(Application $subject, callable $proceed): Response_Interface
    {
        if (!$this->profiler->is_enabled()) {
            return $proceed();
        }
        $return_value = null;
        $this->profiler->do_profile(function () use ($proceed, &$return_value): void {
            $return_value = $proceed();
        }, $subject);
        return $return_value;
    }
}