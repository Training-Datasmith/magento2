<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Sequence\Sequence_Interface;
/**
 * Class EntityMetadata
 */
class Entity_Metadata implements Entity_Metadata_Interface
{
    /**
     * @var ResourceConnection
     */
    protected $resource_connection;
    /**
     * @var string
     */
    protected $entity_table_name;
    /**
     * @var null|string
     */
    protected $connection_name;
    /**
     * @var SequenceInterface
     */
    protected $sequence;
    /**
     * @var string
     */
    protected $eav_entity_type;
    /**
     * @var string
     */
    protected $identifier_field;
    /**
     * @var string[]
     */
    protected $entity_context;
    /**
     * EntityMetadata constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param string $entityTableName
     * @param string $identifierField
     * @param SequenceInterface|null $sequence
     * @param null $eavEntityType
     * @param null $connectionName
     * @param array $entityContext
     */
    public function __construct(Resource_Connection $resource_connection, $entity_table_name, $identifier_field, ?Sequence_Interface $sequence = null, $eav_entity_type = null, $connection_name = null, $entity_context = [])
    {
        $this->resource_connection = $resource_connection;
        $this->entity_table_name = $entity_table_name;
        $this->eav_entity_type = $eav_entity_type;
        $this->connection_name = $connection_name;
        $this->identifier_field = $identifier_field;
        $this->sequence = $sequence;
        $this->entity_context = $entity_context;
    }
    /**
     * @return string
     */
    public function get_identifier_field()
    {
        return $this->identifier_field;
    }
    /**
     * @return string
     */
    public function get_link_field()
    {
        $connection = $this->resource_connection->get_connection_by_name($this->get_entity_connection_name());
        $index_list = $connection->get_index_list($this->get_entity_table());
        return $index_list[$connection->get_primary_key_name($this->get_entity_table())]['COLUMNS_LIST'][0];
    }
    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @deprecated 100.1.0
     */
    public function get_entity_connection()
    {
        return $this->resource_connection->get_connection_by_name($this->connection_name);
    }
    /**
     * @return string
     */
    public function get_entity_table()
    {
        return $this->resource_connection->get_table_name($this->entity_table_name);
    }
    /**
     * @return string
     */
    public function get_entity_connection_name()
    {
        return $this->connection_name;
    }
    /**
     * @return null|string
     */
    public function generate_identifier()
    {
        $next_identifier = null;
        if ($this->sequence) {
            $next_identifier = $this->sequence->get_next_value();
        }
        return $next_identifier;
    }
    /**
     * @return string[]
     */
    public function get_entity_context()
    {
        return $this->entity_context;
    }
    /**
     * @return null|string
     */
    public function get_eav_entity_type()
    {
        return $this->eav_entity_type;
    }
}