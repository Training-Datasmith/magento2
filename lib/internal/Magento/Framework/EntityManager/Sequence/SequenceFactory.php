<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Sequence;

use Magento\Framework\DB\Sequence\Sequence_Interface;
use Magento\Framework\Object_Manager_Interface;
/**
 * Class SequenceFactory
 */
class Sequence_Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @var SequenceRegistry
     */
    protected $sequence_registry;
    /**
     * @var string
     */
    protected $instance_name;
    /**
     * @param SequenceRegistry $sequenceRegistry
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(Sequence_Registry $sequence_registry, Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\Entity_Manager\Sequence\Sequence::class)
    {
        $this->sequence_registry = $sequence_registry;
        $this->object_manager = $object_manager;
        $this->instance_name = $instance_name;
    }
    /**
     * Creates sequence instance
     *
     * @param string $entityType
     * @param array $config
     * @return SequenceInterface
     */
    public function create($entity_type, $config)
    {
        if ($this->sequence_registry->retrieve($entity_type) === false) {
            if (isset($config[$entity_type]['sequence'])) {
                $this->sequence_registry->register($entity_type, $config[$entity_type]['sequence']);
            } elseif (isset($config[$entity_type]['sequenceTable'])) {
                if (isset($config[$entity_type]['connectionName'])) {
                    $connection_name = $config[$entity_type]['connectionName'];
                } else {
                    $connection_name = 'default';
                }
                $this->sequence_registry->register($entity_type, $this->object_manager->create($this->instance_name, ['connectionName' => $connection_name, 'sequenceTable' => $config[$entity_type]['sequenceTable']]), $config[$entity_type]['sequenceTable']);
            } else {
                $this->sequence_registry->register($entity_type);
            }
        }
        return $this->sequence_registry->retrieve($entity_type)['sequence'];
    }
}