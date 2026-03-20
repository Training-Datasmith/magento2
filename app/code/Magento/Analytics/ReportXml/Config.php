<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml;

use Magento\Framework\Config\Data_Interface;
/**
 * Config of ReportXml
 */
class Config implements Config_Interface
{
    /**
     * Config constructor.
     */
    public function __construct(private readonly Data_Interface $data)
    {
    }
    /**
     * Returns config value by name
     *
     * @param string $queryName
     * @return array
     */
    public function get($query_name)
    {
        return $this->data->get($query_name);
    }
}