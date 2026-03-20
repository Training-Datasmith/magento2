<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Sequence;

/**
 * Applier of sequence identifier.
 */
class Sequence_Applier
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadata_pool;
    /**
     * @var \Magento\Framework\EntityManager\TypeResolver
     */
    private $type_resolver;
    /**
     * @var \Magento\Framework\EntityManager\Sequence\SequenceManager
     */
    private $sequence_manager;
    /**
     * @var \Magento\Framework\EntityManager\Sequence\SequenceRegistry
     */
    private $sequence_registry;
    /**
     * @var \Magento\Framework\EntityManager\HydratorPool
     */
    private $hydrator_pool;
    /**
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\EntityManager\TypeResolver $typeResolver
     * @param \Magento\Framework\EntityManager\Sequence\SequenceManager $sequenceManager
     * @param \Magento\Framework\EntityManager\Sequence\SequenceRegistry $sequenceRegistry
     * @param \Magento\Framework\EntityManager\HydratorPool $hydratorPool
     */
    public function __construct(\Magento\Framework\Entity_Manager\Metadata_Pool $metadata_pool, \Magento\Framework\Entity_Manager\Type_Resolver $type_resolver, \Magento\Framework\Entity_Manager\Sequence\Sequence_Manager $sequence_manager, \Magento\Framework\Entity_Manager\Sequence\Sequence_Registry $sequence_registry, \Magento\Framework\Entity_Manager\Hydrator_Pool $hydrator_pool)
    {
        $this->metadata_pool = $metadata_pool;
        $this->type_resolver = $type_resolver;
        $this->sequence_manager = $sequence_manager;
        $this->sequence_registry = $sequence_registry;
        $this->hydrator_pool = $hydrator_pool;
    }
    /**
     * Applies sequence identifier to given entity.
     *
     * In case sequence for given entity is not configured in corresponding di.xml file,
     * the entity will be returned without any changes.
     *
     * @param object $entity
     *
     * @return object
     */
    public function apply($entity)
    {
        $entity_type = $this->type_resolver->resolve($entity);
        /** @var \Magento\Framework\DB\Sequence\SequenceInterface|null $sequence */
        $sequence = $this->sequence_registry->retrieve($entity_type) ? $this->sequence_registry->retrieve($entity_type)['sequence'] : null;
        if ($sequence) {
            $metadata = $this->metadata_pool->get_metadata($entity_type);
            $hydrator = $this->hydrator_pool->get_hydrator($entity_type);
            $entity_data = $hydrator->extract($entity);
            // Object already has identifier.
            if (isset($entity_data[$metadata->get_identifier_field()]) && $entity_data[$metadata->get_identifier_field()]) {
                $this->sequence_manager->force($entity_type, $entity_data[$metadata->get_identifier_field()]);
            } else {
                $entity_data[$metadata->get_identifier_field()] = $sequence->get_next_value();
                $entity = $hydrator->hydrate($entity, $entity_data);
            }
        }
        return $entity;
    }
}