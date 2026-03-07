<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\Config\DataInterface;

/**
 * Config of ReportXml
 */
class Config implements ConfigInterface
{
    /**
     * Config constructor.
     */
    public function __construct(private readonly DataInterface $data)
    {
    }

    /**
     * Returns config value by name
     *
     * @param string $queryName
     * @return array
     */
    public function get($queryName)
    {
        return $this->data->get($queryName);
    }
}
