<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Adapter\Duplicate_Exception;
use Magento\Framework\Entity_Manager\Event_Manager;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Entity_Manager\Operation\Create\Create_Attributes;
use Magento\Framework\Entity_Manager\Operation\Create\Create_Extensions;
use Magento\Framework\Entity_Manager\Operation\Create\Create_Main;
use Magento\Framework\Entity_Manager\Sequence\Sequence_Applier;
use Magento\Framework\Entity_Manager\Type_Resolver;
use Magento\Framework\Exception\Already_Exists_Exception;
use Magento\Framework\Phrase;
/**
 * Class Create
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Create implements Create_Interface
{
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var TypeResolver
     */
    private $type_resolver;
    /**
     * @var ResourceConnection
     */
    private $resource_connection;
    /**
     * @var EventManager
     */
    private $event_manager;
    /**
     * @var CreateMain
     */
    private $create_main;
    /**
     * @var CreateAttributes
     */
    private $create_attributes;
    /**
     * @var CreateExtensions
     */
    private $create_extensions;
    /**
     * @var SequenceApplier
     */
    private $sequence_applier;
    /**
     * @param MetadataPool $metadataPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     * @param EventManager $eventManager
     * @param CreateMain $createMain
     * @param CreateAttributes $createAttributes
     * @param CreateExtensions $createExtensions
     */
    public function __construct(Metadata_Pool $metadata_pool, Type_Resolver $type_resolver, Resource_Connection $resource_connection, Event_Manager $event_manager, Create_Main $create_main, Create_Attributes $create_attributes, Create_Extensions $create_extensions)
    {
        $this->metadata_pool = $metadata_pool;
        $this->type_resolver = $type_resolver;
        $this->resource_connection = $resource_connection;
        $this->event_manager = $event_manager;
        $this->create_main = $create_main;
        $this->create_attributes = $create_attributes;
        $this->create_extensions = $create_extensions;
    }
    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     * @throws AlreadyExistsException
     */
    public function execute($entity, $arguments = [])
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
        $connection->begin_transaction();
        try {
            $this->event_manager->dispatch('entity_manager_save_before', ['entity_type' => $entity_type, 'entity' => $entity]);
            $this->event_manager->dispatch_entity_event($entity_type, 'save_before', ['entity' => $entity]);
            $entity = $this->get_sequence_applier()->apply($entity);
            $entity = $this->create_main->execute($entity, $arguments);
            $entity = $this->create_attributes->execute($entity, $arguments);
            $entity = $this->create_extensions->execute($entity, $arguments);
            $this->event_manager->dispatch_entity_event($entity_type, 'save_after', ['entity' => $entity]);
            $this->event_manager->dispatch('entity_manager_save_after', ['entity_type' => $entity_type, 'entity' => $entity]);
            $connection->commit();
        } catch (Duplicate_Exception $e) {
            $connection->roll_back();
            throw new Already_Exists_Exception(new Phrase('Unique constraint violation found'), $e);
        } catch (\Exception $e) {
            $connection->roll_back();
            throw $e;
        }
        return $entity;
    }
    /**
     * @return SequenceApplier
     *
     * @deprecated 101.0.0
     */
    private function get_sequence_applier()
    {
        if (!$this->sequence_applier) {
            $this->sequence_applier = Object_Manager::get_instance()->get(Sequence_Applier::class);
        }
        return $this->sequence_applier;
    }
}