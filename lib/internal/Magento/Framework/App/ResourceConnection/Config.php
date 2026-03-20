<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Resource_Connection;

use Magento\Framework\Config\Config_Options_List_Constants;
use Magento\Framework\Serialize\Serializer_Interface;
/**
 * Resource configuration, uses application configuration to retrieve resource connection information
 */
class Config extends \Magento\Framework\Config\Data\Scoped implements Config_Interface
{
    /**
     * List of connection names per resource
     *
     * @var array
     */
    protected $_connection_names = [];
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deployment_config;
    /**
     * @var bool
     */
    private $initialized = false;
    /**
     * Constructor
     *
     * @param Config\Reader $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     * @throws \InvalidArgumentException
     */
    public function __construct(Config\Reader $reader, \Magento\Framework\Config\Scope_Interface $config_scope, \Magento\Framework\Config\Cache_Interface $cache, \Magento\Framework\App\Deployment_Config $deployment_config, $cache_id = 'resourcesCache', ?Serializer_Interface $serializer = null)
    {
        parent::__construct($reader, $config_scope, $cache, $cache_id, $serializer);
        $this->deployment_config = $deployment_config;
    }
    /**
     * Retrieve resource connection instance name
     *
     * @param string $resourceName
     * @return string
     */
    public function get_connection_name($resource_name)
    {
        $this->init_connections();
        $connection_name = \Magento\Framework\App\Resource_Connection::DEFAULT_CONNECTION;
        if (!isset($this->_connection_names[$resource_name])) {
            $resources_config = $this->get();
            $pointer_resource_name = $resource_name;
            while (true) {
                if (isset($resources_config[$pointer_resource_name]['connection'])) {
                    $connection_name = $resources_config[$pointer_resource_name]['connection'];
                    $this->_connection_names[$resource_name] = $connection_name;
                    break;
                } elseif (isset($this->_connection_names[$pointer_resource_name])) {
                    $this->_connection_names[$resource_name] = $this->_connection_names[$pointer_resource_name];
                    $connection_name = $this->_connection_names[$resource_name];
                    break;
                } elseif (isset($resources_config[$pointer_resource_name]['extends'])) {
                    $pointer_resource_name = $resources_config[$pointer_resource_name]['extends'];
                } else {
                    break;
                }
            }
        } else {
            $connection_name = $this->_connection_names[$resource_name];
        }
        return $connection_name;
    }
    /**
     * Initialise connections
     *
     * @return void
     */
    private function init_connections()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            $resource = $this->deployment_config->get_config_data(Config_Options_List_Constants::KEY_RESOURCE) ?: [];
            foreach ($resource as $resource_name => $resource_data) {
                if (!isset($resource_data['connection'])) {
                    throw new \InvalidArgumentException('Invalid initial resource configuration');
                }
                $this->_connection_names[$resource_name] = $resource_data['connection'];
            }
        }
    }
}