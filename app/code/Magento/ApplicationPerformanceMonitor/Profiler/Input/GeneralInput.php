<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Profiler\Input;

use Magento\Application_Performance_Monitor\Profiler\Input_Interface;
use Magento\Framework\App_Interface;
/**
 * Adds applicationClass based on the current application
 */
class General_Input implements Input_Interface
{
    /**
     * @inheritDoc
     */
    public function do_input(App_Interface $application): array
    {
        return ['applicationClass' => $application::class];
    }
}