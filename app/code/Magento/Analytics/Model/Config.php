<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Config\Data_Interface;
/**
 * Config of Analytics.
 */
class Config implements Config_Interface
{
    public function __construct(private readonly Data_Interface $data)
    {
    }
    /**
     * Get config value by key.
     *
     * @param string|null $key
     * @param string|null $default
     * @return array
     */
    public function get($key = null, $default = null)
    {
        return $this->data->get($key, $default);
    }
}