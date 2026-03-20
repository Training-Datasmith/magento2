<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Resource_Connection;

/**
 * Interface \Magento\Framework\App\ResourceConnection\ConfigInterface
 *
 * @api
 */
interface Config_Interface
{
    /**
     * Retrieve resource connection instance name
     *
     * @param string $resourceName
     * @return string
     */
    public function get_connection_name($resource_name);
}