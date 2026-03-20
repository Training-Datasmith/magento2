<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

use Magento\Framework\Entity_Manager\Operation\Check_If_Exists_Interface;
use Magento\Framework\Entity_Manager\Operation\Create_Interface;
use Magento\Framework\Entity_Manager\Operation\Delete_Interface;
use Magento\Framework\Entity_Manager\Operation\Read_Interface;
use Magento\Framework\Entity_Manager\Operation\Update_Interface;
/**
 * It's not recommended to use EntityManager and its infrastructure for your entities persistence.
 * In the nearest future new Persistence Entity Manager would be released which will cover all the requirements for
 * persistence layer along with Query API as performance efficient APIs for Read scenarios.
 *
 * Currently, it's recommended to use Resource Model infrastructure and make a successor of
 * Magento\Framework\Model\ResourceModel\Db\AbstractDb class or successor of
 * Magento\Eav\Model\Entity\AbstractEntity if EAV attributes support needed.
 *
 * For filtering operations, it's recommended to use successor of
 * Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection class.
 */
class Entity_Manager
{
    /**
     * @var OperationPool
     */
    private $operation_pool;
    /**
     * @var CallbackHandler
     */
    private $callback_handler;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var TypeResolver
     */
    private $type_resolver;
    /**
     * @param OperationPool $operationPool
     * @param MetadataPool $metadataPool
     * @param TypeResolver $typeResolver
     * @param CallbackHandler $callbackHandler
     */
    public function __construct(Operation_Pool $operation_pool, Metadata_Pool $metadata_pool, Type_Resolver $type_resolver, Callback_Handler $callback_handler)
    {
        $this->operation_pool = $operation_pool;
        $this->metadata_pool = $metadata_pool;
        $this->type_resolver = $type_resolver;
        $this->callback_handler = $callback_handler;
    }
    /**
     * @param object $entity
     * @param string $identifier
     * @param array $arguments
     * @return mixed
     * @throws \LogicException
     */
    public function load($entity, $identifier, $arguments = [])
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $operation = $this->operation_pool->get_operation($entity_type, 'read');
        if (!$operation instanceof Read_Interface) {
            throw new \LogicException(get_class($operation) . ' must implement ' . Read_Interface::class);
        }
        $entity = $operation->execute($entity, $identifier, $arguments);
        return $entity;
    }
    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \LogicException
     * @throws \Exception
     */
    public function save($entity, $arguments = [])
    {
        $entity_type = $this->type_resolver->resolve($entity);
        if ($this->has($entity)) {
            $operation = $this->operation_pool->get_operation($entity_type, 'update');
            if (!$operation instanceof Update_Interface) {
                throw new \LogicException(get_class($operation) . ' must implement ' . Update_Interface::class);
            }
        } else {
            $operation = $this->operation_pool->get_operation($entity_type, 'create');
            if (!$operation instanceof Create_Interface) {
                throw new \LogicException(get_class($operation) . ' must implement ' . Create_Interface::class);
            }
        }
        try {
            $entity = $operation->execute($entity, $arguments);
            $this->callback_handler->process($entity_type);
        } catch (\Exception $e) {
            $this->callback_handler->clear($entity_type);
            throw $e;
        }
        return $entity;
    }
    /**
     * @param object $entity
     * @return bool
     * @throws \LogicException
     */
    public function has($entity)
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $operation = $this->operation_pool->get_operation($entity_type, 'checkIfExists');
        if (!$operation instanceof Check_If_Exists_Interface) {
            throw new \LogicException(get_class($operation) . ' must implement ' . Check_If_Exists_Interface::class);
        }
        return $operation->execute($entity);
    }
    /**
     * @param object $entity
     * @param array $arguments
     * @return bool
     * @throws \LogicException
     * @throws \Exception
     */
    public function delete($entity, $arguments = [])
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $operation = $this->operation_pool->get_operation($entity_type, 'delete');
        if (!$operation instanceof Delete_Interface) {
            throw new \LogicException(get_class($operation) . ' must implement ' . Delete_Interface::class);
        }
        try {
            $operation->execute($entity, $arguments);
            $this->callback_handler->process($entity_type);
        } catch (\Exception $e) {
            $this->callback_handler->clear($entity_type);
            throw $e;
        }
        return true;
    }
}