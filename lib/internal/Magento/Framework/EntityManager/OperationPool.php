<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

use Magento\Framework\Entity_Manager\Operation\Check_If_Exists;
use Magento\Framework\Entity_Manager\Operation\Create;
use Magento\Framework\Entity_Manager\Operation\Delete;
use Magento\Framework\Entity_Manager\Operation\Read;
use Magento\Framework\Entity_Manager\Operation\Update;
use Magento\Framework\Object_Manager_Interface as ObjectManager;
/**
 * Class OperationPool
 */
class Operation_Pool
{
    /**
     * @var array
     */
    private $default_operations = ['checkIfExists' => Check_If_Exists::class, 'read' => Read::class, 'create' => Create::class, 'update' => Update::class, 'delete' => Delete::class];
    /**
     * @var array
     */
    private $operations;
    /**
     * @var ObjectManager
     */
    private $object_manager;
    /**
     * OperationPool constructor.
     * @param ObjectManager $objectManager
     * @param string[] $operations
     */
    public function __construct(Object_Manager $object_manager, $operations = [])
    {
        $this->object_manager = $object_manager;
        $this->operations = array_replace_recursive(['default' => $this->default_operations], $operations);
    }
    /**
     * Returns operation by name by entity type
     *
     * @param string $entityType
     * @param string $operationName
     * @return OperationInterface
     */
    public function get_operation($entity_type, $operation_name)
    {
        if (!isset($this->operations[$entity_type][$operation_name])) {
            return $this->object_manager->get($this->operations['default'][$operation_name]);
        }
        return $this->object_manager->get($this->operations[$entity_type][$operation_name]);
    }
}