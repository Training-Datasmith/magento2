<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Sequence;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Sequence\Sequence_Interface;
/**
 * Class Sequence
 */
class Sequence implements Sequence_Interface
{
    /**
     * @var string
     */
    protected $connection_name;
    /**
     * @var string
     */
    protected $sequence_table;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    /**
     * @param ResourceConnection $resource
     * @param string $connectionName
     * @param string $sequenceTable
     */
    public function __construct(Resource_Connection $resource, $connection_name, $sequence_table)
    {
        $this->resource = $resource;
        $this->connection_name = $connection_name;
        $this->sequence_table = $sequence_table;
    }
    /**
     * @inheritdoc
     */
    public function get_next_value()
    {
        $this->resource->get_connection($this->connection_name)->insert($this->resource->get_table_name($this->sequence_table), []);
        return $this->resource->get_connection($this->connection_name)->last_insert_id($this->resource->get_table_name($this->sequence_table));
    }
    /**
     * @inheritdoc
     */
    public function get_current_value()
    {
        $select = $this->resource->get_connection($this->connection_name)->select();
        $select->from($this->resource->get_table_name($this->sequence_table));
        return $this->resource->get_connection($this->connection_name)->fetch_row($select);
    }
}