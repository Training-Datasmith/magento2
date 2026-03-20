<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Object_Manager;

/**
 * Write compiled object manager configuration to storage
 *
 * @api
 */
interface Config_Writer_Interface
{
    /**
     * Writes config in storage
     *
     * @param string $key
     * @param array $config
     * @return void
     */
    public function write(string $key, array $config);
}