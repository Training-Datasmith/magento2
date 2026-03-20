<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Data;

/**
 * Processes data from admin store configuration fields
 *
 * @api
 * @since 100.0.2
 */
interface Processor_Interface
{
    /**
     * Process config value
     *
     * @param string $value Raw value of the configuration field
     * @return string Processed value
     */
    public function process_value($value);
}