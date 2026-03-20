<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Adapter\Adapter_Interface;
/**
 * Class Concat
 */
class Sub_Select extends \Zend_Db_Expr
{
    /**
     * @var string
     */
    protected $table;
    /**
     * @var string[]
     */
    protected $columns;
    /**
     * @var string
     */
    protected $origin_column;
    /**
     * @var string
     */
    protected $target_column;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    /**
     * @var string
     */
    protected $connection_name;
    /**
     * @var AdapterInterface
     */
    protected $connection;
    /**
     * @param ResourceConnection $resource
     * @param string $connectionName
     * @param string $table
     * @param string[] $columns
     * @param string $originColumn
     * @param string $targetColumn
     */
    public function __construct(Resource_Connection $resource, $table, array $columns, $origin_column, $target_column, $connection_name = Resource_Connection::DEFAULT_CONNECTION)
    {
        $this->resource = $resource;
        $this->connection_name = $connection_name;
        $this->table = $table;
        $this->columns = $columns;
        $this->origin_column = $origin_column;
        $this->target_column = $target_column;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        $select = $this->get_connection()->select()->from($this->resource->get_table_name($this->table), array_values($this->columns))->where(sprintf('%s = %s', $this->get_connection()->quote_identifier($this->origin_column), $this->get_connection()->quote_identifier($this->target_column)))->limit(1);
        return sprintf('(%s)', $select);
    }
    /**
     * Returns connection
     *
     * @return AdapterInterface
     */
    protected function get_connection()
    {
        if (!$this->connection) {
            $this->connection = $this->resource->get_connection($this->connection_name);
        }
        return $this->connection;
    }
}