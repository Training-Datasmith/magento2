<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\App\Resource_Connection\Config_Interface as ResourceConfigInterface;
use Magento\Framework\Config\Config_Options_List_Constants;
use Magento\Framework\Model\Resource_Model\Type\Db\Connection_Factory_Interface;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * Application provides ability to configure multiple connections to persistent storage.
 *
 * This class provides access to all these connections.
 *
 * @api
 * @since 100.0.2
 */
class Resource_Connection implements Reset_After_Request_Interface
{
    public const AUTO_UPDATE_ONCE = 0;
    public const AUTO_UPDATE_NEVER = -1;
    public const AUTO_UPDATE_ALWAYS = 1;
    public const DEFAULT_CONNECTION = 'default';
    /**
     * Instances of actual connections.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface[]
     */
    protected $connections = [];
    /**
     * Mapped tables cache array.
     *
     * @var array
     */
    protected $mapped_table_names = [];
    /**
     * Resource config.
     *
     * @var ResourceConfigInterface
     */
    protected $config;
    /**
     * Resource connection adapter factory.
     *
     * @var ConnectionFactoryInterface
     */
    protected $connection_factory;
    /**
     * @var DeploymentConfig
     */
    private $deployment_config;
    /**
     * @var string
     */
    protected $table_prefix;
    /**
     * @param ResourceConfigInterface $resourceConfig
     * @param ConnectionFactoryInterface $connectionFactory
     * @param DeploymentConfig $deploymentConfig
     * @param string $tablePrefix
     */
    public function __construct(Resource_Config_Interface $resource_config, Connection_Factory_Interface $connection_factory, Deployment_Config $deployment_config, $table_prefix = '')
    {
        $this->config = $resource_config;
        $this->connection_factory = $connection_factory;
        $this->deployment_config = $deployment_config;
        $this->table_prefix = $table_prefix ?: null;
    }
    /**
     * @inheritdoc
     */
    public function _reset_state(): void
    {
        $this->mapped_table_names = [];
        foreach ($this->connections as $connection) {
            if ($connection instanceof Reset_After_Request_Interface) {
                $connection->_reset_state();
            }
        }
    }
    /**
     * Retrieve connection to resource specified by $resourceName.
     *
     * @param string $resourceName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     * @codeCoverageIgnore
     */
    public function get_connection($resource_name = self::DEFAULT_CONNECTION)
    {
        $connection_name = $this->config->get_connection_name($resource_name);
        return $this->get_connection_by_name($connection_name);
    }
    /**
     * Close connection.
     *
     * @param string $resourceName
     * @return void
     * @since 100.1.3
     */
    public function close_connection($resource_name = self::DEFAULT_CONNECTION)
    {
        if ($resource_name === null) {
            foreach ($this->connections as $process_connection) {
                if ($process_connection !== null) {
                    $process_connection->close_connection();
                }
            }
            $this->connections = [];
        } else {
            $process_connection_name = $this->config->get_connection_name($resource_name) ?? '';
            if (isset($this->connections[$process_connection_name])) {
                if ($this->connections[$process_connection_name] !== null) {
                    $this->connections[$process_connection_name]->close_connection();
                }
                $this->connections[$process_connection_name] = null;
            }
        }
    }
    /**
     * Retrieve connection by $connectionName.
     *
     * @param string $connectionName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     */
    public function get_connection_by_name($connection_name)
    {
        if (isset($this->connections[$connection_name])) {
            return $this->connections[$connection_name];
        }
        $connection_config = $this->deployment_config->get(Config_Options_List_Constants::CONFIG_PATH_DB_CONNECTIONS . '/' . $connection_name);
        if ($connection_config) {
            $connection = $this->connection_factory->create($connection_config);
        } else {
            throw new \DomainException('Connection "' . $connection_name . '" is not defined');
        }
        $this->connections[$connection_name] = $connection;
        return $connection;
    }
    /**
     * Get resource table name, validated by db adapter.
     *
     * @param string|string[] $modelEntity
     * @param string $connectionName
     * @return string
     */
    public function get_table_name($model_entity, $connection_name = self::DEFAULT_CONNECTION)
    {
        $table_suffix = null;
        if (is_array($model_entity)) {
            list($model_entity, $table_suffix) = $model_entity;
        }
        $table_name = (string) $model_entity;
        $mapped_table_name = $this->get_mapped_table_name($table_name);
        if ($mapped_table_name) {
            $table_name = $mapped_table_name;
        } else {
            $table_prefix = $this->get_table_prefix();
            if ($table_prefix && strpos($table_name, $table_prefix) !== 0) {
                $table_name = $table_prefix . $table_name;
            }
        }
        if ($table_suffix) {
            $table_name .= '_' . $table_suffix;
        }
        return $this->get_connection($connection_name)->get_table_name($table_name);
    }
    /**
     * Gets table placeholder by table name.
     *
     * @param string $tableName
     * @return string
     * @since 100.1.0
     */
    public function get_table_placeholder($table_name)
    {
        $table_name = preg_replace('/^' . preg_quote($this->get_table_prefix()) . '_/', '', $table_name);
        return $table_name;
    }
    /**
     * Build a trigger name.
     *
     * @param string $tableName The table that is the subject of the trigger
     * @param string $time Either "before" or "after"
     * @param string $event The DB level event which activates the trigger, i.e. "update" or "insert"
     * @return string
     */
    public function get_trigger_name($table_name, $time, $event)
    {
        return $this->get_connection()->get_trigger_name($table_name, $time, $event);
    }
    /**
     * Set mapped table name.
     *
     * @param string $tableName
     * @param string $mappedName
     * @return $this
     * @codeCoverageIgnore
     */
    public function set_mapped_table_name($table_name, $mapped_name)
    {
        $this->mapped_table_names[$table_name] = $mapped_name;
        return $this;
    }
    /**
     * Get mapped table name.
     *
     * @param string $tableName
     * @return bool|string
     */
    public function get_mapped_table_name($table_name)
    {
        if (isset($this->mapped_table_names[$table_name])) {
            return $this->mapped_table_names[$table_name];
        } else {
            return false;
        }
    }
    /**
     * Retrieve 32bit UNIQUE HASH for a Table index.
     *
     * @param string $tableName
     * @param string|string[] $fields
     * @param string $indexType
     * @return string
     */
    public function get_idx_name($table_name, $fields, $index_type = \Magento\Framework\DB\Adapter\Adapter_Interface::INDEX_TYPE_INDEX)
    {
        return $this->get_connection()->get_index_name($this->get_table_name($table_name), $fields, $index_type);
    }
    /**
     * Retrieve 32bit UNIQUE HASH for a Table foreign key.
     *
     * @param string $priTableName the target table name
     * @param string $priColumnName the target table column name
     * @param string $refTableName the reference table name
     * @param string $refColumnName the reference table column name
     * @return string
     */
    public function get_fk_name($pri_table_name, $pri_column_name, $ref_table_name, $ref_column_name)
    {
        return $this->get_connection()->get_foreign_key_name($this->get_table_name($pri_table_name), $pri_column_name, $this->get_table_name($ref_table_name), $ref_column_name);
    }
    /**
     * Retrieve db name.
     *
     * That name can be needed, when we do request in information_schema to identify db.
     *
     * @param string $resourceName
     * @return string
     * @since 102.0.0
     */
    public function get_schema_name($resource_name)
    {
        return $this->deployment_config->get(Config_Options_List_Constants::CONFIG_PATH_DB_CONNECTIONS . '/' . $resource_name . '/dbname');
    }
    /**
     * Get table prefix.
     *
     * @return string
     */
    public function get_table_prefix()
    {
        if ($this->table_prefix !== null) {
            return $this->table_prefix;
        }
        return (string) $this->deployment_config->get(Config_Options_List_Constants::CONFIG_PATH_DB_PREFIX);
    }
}