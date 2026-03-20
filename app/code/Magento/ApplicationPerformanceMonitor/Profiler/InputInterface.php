<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Application_Performance_Monitor\Profiler;

use Magento\Framework\App_Interface as Application;
/**
 * Interface for adding additional information.
 */
interface Input_Interface
{
    /**
     * Input for other information
     */
    public function do_input(Application $application): array;
}