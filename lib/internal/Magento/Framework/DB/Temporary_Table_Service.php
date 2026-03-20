<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\Adapter_Interface;
/**
 * Class TemporaryTableService creates a temporary table in mysql from a Magento\Framework\DB\Select.
 * Use this class to create an index with that you want to query later for quick data access
 *
 * @api
 * @since 100.1.8
 */
class Temporary_Table_Service
{
    public const INDEX_METHOD_HASH = 'HASH';
    public const DB_ENGINE_INNODB = 'INNODB';
    /**
     * @var string[]
     */
    private $allowed_index_methods;
    /**
     * @var string[]
     */
    private $allowed_engines;
    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;
    /**
     * @var AdapterInterface[]
     */
    private $created_table_adapters = [];
    /**
     * @param \Magento\Framework\Math\Random $random
     * @param string[] $allowedIndexMethods
     * @param string[] $allowedEngines
     */
    public function __construct(\Magento\Framework\Math\Random $random, $allowed_index_methods = [], $allowed_engines = [])
    {
        $this->random = $random;
        $this->allowed_index_methods = $allowed_index_methods;
        $this->allowed_engines = $allowed_engines;
    }
    /**
     * Creates a temporary table from select removing duplicate rows if you have a union in your select
     * This method should always be paired with dropTable to ensure cleanup
     * Make sure you index your data so you can query it fast
     * You can choose from memory or file table and provide indexes to ensure fast data query
     *
     * Example: createFromSelect(
     *           $selectObject,
     *           $this->resourceConnection->getConnection(),
     *           [
     *              'PRIMARY' => ['primary_id'],
     *              'some_single_field_index' => ['field'],
     *              'UNQ_some_multiple_field_index' => ['field1', 'field2'],
     *           ]
     *          )
     * Note that indexes names with UNQ_ prefix, will be created as unique
     *
     * @param Select $select
     * @param AdapterInterface $adapter
     * @param array $indexes
     * @param string $indexMethod
     * @param string $dbEngine
     * @return string
     * @throws \InvalidArgumentException
     * @since 100.1.8
     */
    public function create_from_select(Select $select, Adapter_Interface $adapter, array $indexes = [], $index_method = self::INDEX_METHOD_HASH, $db_engine = self::DB_ENGINE_INNODB)
    {
        if (!in_array($index_method, $this->allowed_index_methods)) {
            throw new \InvalidArgumentException(sprintf('indexMethod must be one of %s', implode(',', $this->allowed_index_methods)));
        }
        if (!in_array($db_engine, $this->allowed_engines)) {
            throw new \InvalidArgumentException(sprintf('dbEngine must be one of %s', implode(',', $this->allowed_engines)));
        }
        $name = $this->random->get_unique_hash('tmp_select_');
        $index_statements = [];
        foreach ($indexes as $index_name => $columns) {
            $rendered_columns = implode(',', array_map([$adapter, 'quoteIdentifier'], $columns));
            $index_type = sprintf('INDEX %s USING %s', $adapter->quote_identifier($index_name), $index_method);
            if ($index_name === 'PRIMARY') {
                $index_type = 'PRIMARY KEY';
            } elseif (strpos($index_name, 'UNQ_') === 0) {
                $index_type = sprintf('UNIQUE %s', $adapter->quote_identifier($index_name));
            }
            $index_statements[] = sprintf('%s(%s)', $index_type, $rendered_columns);
        }
        $statement = sprintf('CREATE TEMPORARY TABLE %s %s ENGINE=%s IGNORE (%s)', $adapter->quote_identifier($name), $index_statements ? '(' . implode(',', $index_statements) . ')' : '', $adapter->quote_identifier($db_engine), "{$select}");
        $adapter->query($statement, $select->get_bind());
        $this->created_table_adapters[$name] = $adapter;
        return $name;
    }
    /**
     * Method used to drop a table by name
     * This class will hold all temporary table names in createdTableAdapters array
     * so we can dispose them once we're finished
     *
     * Example: dropTable($name)
     * where $name is a variable that holds the name for a previously created temporary  table
     * by using "createFromSelect" method
     *
     * @param string $name
     * @return bool
     * @since 100.1.8
     */
    public function drop_table($name)
    {
        if (!empty($this->created_table_adapters)) {
            if (isset($this->created_table_adapters[$name]) && !empty($name)) {
                $adapter = $this->created_table_adapters[$name];
                $adapter->drop_temporary_table($name);
                unset($this->created_table_adapters[$name]);
                return true;
            }
        }
        return false;
    }
}