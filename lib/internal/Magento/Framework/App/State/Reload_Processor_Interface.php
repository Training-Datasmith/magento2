<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\State;

interface Reload_Processor_Interface
{
    /**
     * Tells the system state to reload itself.
     *
     * @return void
     */
    public function reload_state(): void;
}