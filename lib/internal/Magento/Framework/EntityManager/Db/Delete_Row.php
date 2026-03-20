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
class Delete_Row
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
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function execute($entity_type, $data)
    {
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
        return $connection->delete($metadata->get_entity_table(), [$metadata->get_link_field() . ' = ?' => $data[$metadata->get_link_field()]]);
    }
}