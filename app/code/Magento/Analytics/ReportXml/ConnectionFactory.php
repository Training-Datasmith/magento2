<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Resource_Connection\Config_Interface as ResourceConfigInterface;
use Magento\Framework\Config\Config_Options_List_Constants;
use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\Model\Resource_Model\Type\Db\Connection_Factory_Interface;
/**
 * Creates connection instance for export according to existing one
 *
 * This connection does not use buffered statement, also this connection is not persistent
 */
class Connection_Factory
{
    public function __construct(private readonly Resource_Config_Interface $resource_config, private readonly Deployment_Config $deployment_config, private readonly Connection_Factory_Interface $connection_factory)
    {
    }
    /**
     * Creates one-time connection for export
     *
     * @param string $resourceName
     * @return AdapterInterface
     */
    public function get_connection($resource_name)
    {
        $connection_name = $this->resource_config->get_connection_name($resource_name);
        $config_data = $this->deployment_config->get(Config_Options_List_Constants::CONFIG_PATH_DB_CONNECTIONS . '/' . $connection_name);
        $config_data['use_buffered_query'] = false;
        unset($config_data['persistent']);
        return $this->connection_factory->create($config_data);
    }
}