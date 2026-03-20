<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation;

use Magento\Framework\Entity_Manager\Event_Manager;
use Magento\Framework\Entity_Manager\Hydrator_Pool;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Entity_Manager\Operation\Read\Read_Attributes;
use Magento\Framework\Entity_Manager\Operation\Read\Read_Extensions;
use Magento\Framework\Entity_Manager\Operation\Read\Read_Main;
use Magento\Framework\Entity_Manager\Type_Resolver;
/**
 * Class Read
 */
class Read implements Read_Interface
{
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var HydratorPool
     */
    private $hydrator_pool;
    /**
     * @var EventManager
     */
    private $event_manager;
    /**
     * @var TypeResolver
     */
    private $type_resolver;
    /**
     * @var ReadMain
     */
    private $read_main;
    /**
     * @var ReadAttributes
     */
    private $read_attributes;
    /**
     * @var ReadAttributes
     */
    private $read_extensions;
    /**
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param TypeResolver $typeResolver
     * @param EventManager $eventManager
     * @param ReadMain $readMain
     * @param ReadAttributes $readAttributes
     * @param ReadExtensions $readExtensions
     */
    public function __construct(Metadata_Pool $metadata_pool, Hydrator_Pool $hydrator_pool, Type_Resolver $type_resolver, Event_Manager $event_manager, Read_Main $read_main, Read_Attributes $read_attributes, Read_Extensions $read_extensions)
    {
        $this->metadata_pool = $metadata_pool;
        $this->hydrator_pool = $hydrator_pool;
        $this->type_resolver = $type_resolver;
        $this->event_manager = $event_manager;
        $this->read_main = $read_main;
        $this->read_attributes = $read_attributes;
        $this->read_extensions = $read_extensions;
    }
    /**
     * {@inheritDoc}
     */
    public function execute($entity, $identifier, $arguments = [])
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        $hydrator = $this->hydrator_pool->get_hydrator($entity_type);
        $this->event_manager->dispatch('entity_manager_load_before', ['entity_type' => $entity_type, 'identifier' => $identifier, 'arguments' => $arguments]);
        $this->event_manager->dispatch_entity_event($entity_type, 'load_before', ['identifier' => $identifier, 'entity' => $entity, 'arguments' => $arguments]);
        $entity = $this->read_main->execute($entity, $identifier);
        $entity_data = array_merge($hydrator->extract($entity), $arguments);
        if (isset($entity_data[$metadata->get_link_field()])) {
            $entity = $this->read_attributes->execute($entity, $arguments);
            $entity = $this->read_extensions->execute($entity, $arguments);
        }
        $this->event_manager->dispatch_entity_event($entity_type, 'load_after', ['entity' => $entity, 'arguments' => $arguments]);
        $this->event_manager->dispatch('entity_manager_load_after', ['entity_type' => $entity_type, 'entity' => $entity, 'arguments' => $arguments]);
        return $entity;
    }
}