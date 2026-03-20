<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Spi;

/**
 * Allows to use custom callbacks and functions before applying fallback
 *
 * @api
 */
interface Pre_Processor_Interface
{
    /**
     * Pre-processing of config
     *
     * @param array $config
     * @return array
     */
    public function process(array $config);
}