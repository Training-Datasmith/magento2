<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Db;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Entity_Manager\Metadata_Pool;
/**
 * Class DeleteRow
 */
class Read_Row
{
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var ResourceConnection
     */
    private $resource_connection;
    /**
     * CreateRow constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(Metadata_Pool $metadata_pool, Resource_Connection $resource_connection)
    {
        $this->metadata_pool = $metadata_pool;
        $this->resource_connection = $resource_connection;
    }
    /**
     * @param string $entityType
     * @param string $identifier
     * @param array $context
     * @return array
     * @throws \Exception
     */
    public function execute($entity_type, $identifier, $context = [])
    {
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
        $select = $connection->select()->from(['t' => $metadata->get_entity_table()])->where($metadata->get_identifier_field() . ' = ?', $identifier);
        foreach ($context as $field => $value) {
            $select->where($connection->quote_identifier($field) . ' = ?', $value);
        }
        $data = $connection->fetch_row($select);
        return $data ?: [];
    }
}