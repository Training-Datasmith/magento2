<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Entity;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Entity_Manager\Mapper_Interface;
use Magento\Framework\Entity_Manager\Metadata_Pool;
/**
 * @deprecated 100.2.0
 */
class Bulk_Summary_Mapper implements Mapper_Interface
{
    public function __construct(private readonly Metadata_Pool $metadata_pool, private readonly Resource_Connection $resource_connection)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function entity_to_database($entity_type, $data)
    {
        // workaround for delete/update operations that are currently using only primary key as identifier
        if (!empty($data['uuid'])) {
            $metadata = $this->metadata_pool->get_metadata($entity_type);
            $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
            $select = $connection->select()->from($metadata->get_entity_table(), 'id')->where('uuid = ?', $data['uuid']);
            $identifier = $connection->fetch_one($select);
            if ($identifier !== false) {
                $data['id'] = $identifier;
            }
        }
        return $data;
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function database_to_entity($entity_type, $data)
    {
        return $data;
    }
}