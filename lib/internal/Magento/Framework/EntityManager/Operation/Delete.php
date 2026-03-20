<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Entity_Manager\Event_Manager;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Entity_Manager\Operation\Delete\Delete_Attributes;
use Magento\Framework\Entity_Manager\Operation\Delete\Delete_Extensions;
use Magento\Framework\Entity_Manager\Operation\Delete\Delete_Main;
use Magento\Framework\Entity_Manager\Type_Resolver;
use Magento\Framework\Model\Resource_Model\Db\Transaction_Manager_Interface;
/**
 * Class Delete
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Delete implements Delete_Interface
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
     * @var TransactionManagerInterface
     */
    private $transaction_manager;
    /**
     * @var DeleteMain
     */
    private $delete_main;
    /**
     * @var DeleteAttributes
     */
    private $delete_attributes;
    /**
     * @var DeleteExtensions
     */
    private $delete_extensions;
    /**
     * @param MetadataPool $metadataPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     * @param EventManager $eventManager
     * @param TransactionManagerInterface $transactionManager
     * @param DeleteMain $deleteMain
     * @param DeleteAttributes $deleteAttributes
     * @param DeleteExtensions $deleteExtensions
     */
    public function __construct(Metadata_Pool $metadata_pool, Type_Resolver $type_resolver, Resource_Connection $resource_connection, Event_Manager $event_manager, Transaction_Manager_Interface $transaction_manager, Delete_Main $delete_main, Delete_Attributes $delete_attributes, Delete_Extensions $delete_extensions)
    {
        $this->metadata_pool = $metadata_pool;
        $this->type_resolver = $type_resolver;
        $this->resource_connection = $resource_connection;
        $this->event_manager = $event_manager;
        $this->transaction_manager = $transaction_manager;
        $this->delete_main = $delete_main;
        $this->delete_attributes = $delete_attributes;
        $this->delete_extensions = $delete_extensions;
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
        $this->transaction_manager->start($connection);
        try {
            $this->event_manager->dispatch('entity_manager_delete_before', ['entity_type' => $entity_type, 'entity' => $entity]);
            $this->event_manager->dispatch_entity_event($entity_type, 'delete_before', ['entity' => $entity]);
            $entity = $this->delete_extensions->execute($entity, $arguments);
            $entity = $this->delete_attributes->execute($entity, $arguments);
            $entity = $this->delete_main->execute($entity, $arguments);
            $this->event_manager->dispatch_entity_event($entity_type, 'delete_after', ['entity' => $entity]);
            $this->event_manager->dispatch('entity_manager_delete_after', ['entity_type' => $entity_type, 'entity' => $entity]);
            $this->transaction_manager->commit();
        } catch (\Exception $e) {
            $this->transaction_manager->roll_back();
            throw $e;
        }
        return $entity;
    }
}