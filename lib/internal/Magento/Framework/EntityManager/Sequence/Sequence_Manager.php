<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Sequence;

use Magento\Framework\Entity_Manager\Metadata_Pool;
use Psr\Log\Logger_Interface;
/**
 * Class SequenceManager
 */
class Sequence_Manager
{
    /**
     * @var SequenceRegistry
     */
    private $sequence_registry;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $app_resource;
    /**
     * @param MetadataPool $metadataPool
     * @param SequenceRegistry $sequenceRegistry
     * @param LoggerInterface $logger
     * @param \Magento\Framework\App\ResourceConnection $appResource
     */
    public function __construct(Metadata_Pool $metadata_pool, Sequence_Registry $sequence_registry, Logger_Interface $logger, \Magento\Framework\App\Resource_Connection $app_resource)
    {
        $this->metadata_pool = $metadata_pool;
        $this->sequence_registry = $sequence_registry;
        $this->logger = $logger;
        $this->app_resource = $app_resource;
    }
    /**
     * Forces creation of a sequence value.
     *
     * @param string $entityType
     * @param string|int $identifier
     *
     * @return int
     *
     * @throws \Exception
     */
    public function force($entity_type, $identifier)
    {
        $sequence_info = $this->sequence_registry->retrieve($entity_type);
        if (!isset($sequence_info['sequenceTable'])) {
            throw new \Exception('TODO: use correct Exception class' . PHP_EOL . ' Sequence table doesn\'t exists');
        }
        try {
            $metadata = $this->metadata_pool->get_metadata($entity_type);
            $connection = $this->app_resource->get_connection_by_name($metadata->get_entity_connection_name());
            return $connection->insert($this->app_resource->get_table_name($sequence_info['sequenceTable']), ['sequence_value' => $identifier]);
        } catch (\Exception $e) {
            $this->logger->critical($e->get_message(), $e->get_trace());
            throw new \Exception('TODO: use correct Exception class' . PHP_EOL . $e->get_message());
        }
    }
    /**
     * @param string $entityType
     * @param int $identifier
     * @return int
     * @throws \Exception
     */
    public function delete($entity_type, $identifier)
    {
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        $sequence_info = $this->sequence_registry->retrieve($entity_type);
        if (!isset($sequence_info['sequenceTable'])) {
            throw new \Exception('TODO: use correct Exception class' . PHP_EOL . ' Sequence table doesn\'t exists');
        }
        try {
            $connection = $this->app_resource->get_connection_by_name($metadata->get_entity_connection_name());
            return $connection->delete($this->app_resource->get_table_name($sequence_info['sequenceTable']), ['sequence_value = ?' => $identifier]);
        } catch (\Exception $e) {
            $this->logger->critical($e->get_message(), $e->get_trace());
            throw new \Exception('TODO: use correct Exception class' . PHP_EOL . $e->get_message());
        }
    }
}