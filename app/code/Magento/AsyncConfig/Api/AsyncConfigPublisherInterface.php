<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Async_Config\Api;

use Magento\Framework\Exception\File_System_Exception;
interface Async_Config_Publisher_Interface
{
    /**
     * Save Configuration Data
     *
     * @return void
     * @throws FileSystemException
     */
    public function save_config_data(array $config_data);
}