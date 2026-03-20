<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

use Magento\Framework\Entity_Manager\Sequence\Sequence_Factory;
use Magento\Framework\Object_Manager_Interface;
/**
 * Class MetadataPool
 *
 * @api
 * @since 100.1.0
 */
class Metadata_Pool
{
    /**
     * @var ObjectManagerInterface
     * @since 100.1.0
     */
    protected $object_manager;
    /**
     * @var array
     * @since 100.1.0
     */
    protected $metadata;
    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata[]
     * @since 100.1.0
     */
    protected $registry;
    /**
     * @var SequenceFactory
     * @since 100.1.0
     */
    protected $sequence_factory;
    /**
     * MetadataPool constructor.
     * @param ObjectManagerInterface $objectManager
     * @param SequenceFactory $sequenceFactory
     * @param array $metadata
     */
    public function __construct(Object_Manager_Interface $object_manager, Sequence_Factory $sequence_factory, array $metadata)
    {
        $this->object_manager = $object_manager;
        $this->sequence_factory = $sequence_factory;
        $this->metadata = $metadata;
    }
    /**
     * @param string $entityType
     * @return EntityMetadataInterface
     */
    private function create_metadata($entity_type)
    {
        //@todo: use ID as default if , check is type has EAV attributes
        $connection_name = isset($this->metadata[$entity_type]['connectionName']) ? $this->metadata[$entity_type]['connectionName'] : 'default';
        $eav_entity_type = isset($this->metadata[$entity_type]['eavEntityType']) ? $this->metadata[$entity_type]['eavEntityType'] : null;
        $entity_context = isset($this->metadata[$entity_type]['entityContext']) ? $this->metadata[$entity_type]['entityContext'] : [];
        return $this->object_manager->create(Entity_Metadata_Interface::class, ['entityTableName' => $this->metadata[$entity_type]['entityTableName'], 'eavEntityType' => $eav_entity_type, 'connectionName' => $connection_name, 'identifierField' => $this->metadata[$entity_type]['identifierField'], 'sequence' => $this->sequence_factory->create($entity_type, $this->metadata), 'entityContext' => $entity_context]);
    }
    /**
     * @param string $entityType
     * @return EntityMetadataInterface
     * @throws \Exception
     * @since 100.1.0
     */
    public function get_metadata($entity_type)
    {
        if (!isset($this->metadata[$entity_type])) {
            throw new \Exception(sprintf('Unknown entity type: %s requested', $entity_type));
        }
        if (!isset($this->registry[$entity_type])) {
            $this->registry[$entity_type] = $this->create_metadata($entity_type);
        }
        return $this->registry[$entity_type];
    }
    /**
     * @param string $entityType
     * @return HydratorInterface
     * @deprecated 100.1.0
     * @since 100.1.0
     */
    public function get_hydrator($entity_type)
    {
        $object_manager = \Magento\Framework\App\Object_Manager::get_instance();
        return $object_manager->get(Hydrator_Pool::class)->get_hydrator($entity_type);
    }
    /**
     * Check if entity type configuration was set to metadata
     *
     * @param string $entityType
     * @return bool
     * @since 100.1.0
     */
    public function has_configuration($entity_type)
    {
        return isset($this->metadata[$entity_type]);
    }
}