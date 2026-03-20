<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml;

/**
 * Interface ConfigInterface
 *
 * Interface for ReportXml Config
 */
interface Config_Interface
{
    /**
     * Config of ReportXml
     *
     * @param string $queryName
     * @return array
     */
    public function get($query_name);
}