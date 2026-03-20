<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Spi;

use Magento\Framework\App\Config\Config_Type_Interface;
use Magento\Framework\App\Config\Reader\Source\Source_Interface;
/**
 * Allows to use custom callbacks and functions after collecting config from all sources
 *
 * @see SourceInterface
 * @see ConfigTypeInterface
 * @package Magento\Framework\App\Config\Spi
 * @api
 */
interface Post_Processor_Interface
{
    /**
     * Process config after reading and converting to appropriate format
     *
     * @param array $config
     * @return array
     */
    public function process(array $config);
}