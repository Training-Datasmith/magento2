<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Async_Config\Api\Data;

interface Async_Config_Message_Interface
{
    /**
     * Get Configuration data
     *
     * @return string
     */
    public function get_config_data();
    /**
     * Set Configuration data
     *
     * @return void
     */
    public function set_config_data(string $data);
}