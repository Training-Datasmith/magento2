<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\Config\DataInterface;

/**
 * Config of Analytics.
 */
class Config implements ConfigInterface
{
    public function __construct(private readonly DataInterface $data)
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
