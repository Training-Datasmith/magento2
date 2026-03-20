<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Db;

use Exception;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\Entity_Manager\Entity_Metadata_Interface;
use Magento\Framework\Entity_Manager\Metadata_Pool;
class Create_Row
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
     * Method to prepare data.
     *
     * @param EntityMetadataInterface $metadata
     * @param AdapterInterface $connection
     * @param array $data
     * @return array
     */
    protected function prepare_data(Entity_Metadata_Interface $metadata, Adapter_Interface $connection, $data)
    {
        $output = [];
        foreach ($connection->describe_table($metadata->get_entity_table()) as $column) {
            $column_name = strtolower($column['COLUMN_NAME'] ?? '');
            if ($this->can_not_set_time_stamp($column_name, $column, $data)) {
                continue;
            }
            if (isset($data[$column_name])) {
                $output[strtolower($column['COLUMN_NAME'] ?? '')] = $data[strtolower($column['COLUMN_NAME'] ?? '')];
            } elseif ($column['DEFAULT'] === null) {
                $output[strtolower($column['COLUMN_NAME'])] = null;
            }
        }
        if (empty($data[$metadata->get_identifier_field()])) {
            $output[$metadata->get_identifier_field()] = $metadata->generate_identifier();
        }
        return $output;
    }
    /**
     * Method to can not set time stamp.
     *
     * @param string $columnName
     * @param string $column
     * @param array $data
     * @return bool
     */
    private function can_not_set_time_stamp($column_name, $column, array $data)
    {
        return $column['DEFAULT'] == 'CURRENT_TIMESTAMP' && !isset($data[$column_name]) && empty($column['NULLABLE']);
    }
    /**
     * Method to execute.
     *
     * @param string $entityType
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function execute($entity_type, $data)
    {
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        $link_field = $metadata->get_link_field();
        $entity_table = $metadata->get_entity_table();
        $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
        $connection->insert($entity_table, $this->prepare_data($metadata, $connection, $data));
        if (!isset($data[$link_field]) || !$data[$link_field]) {
            $data[$link_field] = $connection->last_insert_id($entity_table);
        }
        return $data;
    }
}