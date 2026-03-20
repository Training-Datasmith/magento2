<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Adapter\Duplicate_Exception;
use Magento\Framework\Entity_Manager\Event_Manager;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Entity_Manager\Operation\Update\Update_Attributes;
use Magento\Framework\Entity_Manager\Operation\Update\Update_Extensions;
use Magento\Framework\Entity_Manager\Operation\Update\Update_Main;
use Magento\Framework\Entity_Manager\Type_Resolver;
use Magento\Framework\Exception\Already_Exists_Exception;
use Magento\Framework\Phrase;
/**
 * Class Update
 */
class Update implements Update_Interface
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
     * @var UpdateMain
     */
    private $update_main;
    /**
     * @var UpdateAttributes
     */
    private $update_attributes;
    /**
     * @var UpdateExtensions
     */
    private $update_extensions;
    /**
     * @param MetadataPool $metadataPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     * @param EventManager $eventManager
     * @param UpdateMain $updateMain
     * @param UpdateAttributes $updateAttributes
     * @param UpdateExtensions $updateExtensions
     */
    public function __construct(Metadata_Pool $metadata_pool, Type_Resolver $type_resolver, Resource_Connection $resource_connection, Event_Manager $event_manager, Update_Main $update_main, Update_Attributes $update_attributes, Update_Extensions $update_extensions)
    {
        $this->metadata_pool = $metadata_pool;
        $this->type_resolver = $type_resolver;
        $this->resource_connection = $resource_connection;
        $this->event_manager = $event_manager;
        $this->update_main = $update_main;
        $this->update_attributes = $update_attributes;
        $this->update_extensions = $update_extensions;
    }
    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
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
            $entity = $this->update_main->execute($entity, $arguments);
            $entity = $this->update_attributes->execute($entity, $arguments);
            $entity = $this->update_extensions->execute($entity, $arguments);
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
}