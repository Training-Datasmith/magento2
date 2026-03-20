<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation;

use Magento\Framework\Object_Manager_Interface;
/**
 * Class AttributePool
 */
class Attribute_Pool
{
    /**
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * @var object[]
     */
    private $actions;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $extensionActions
     */
    public function __construct(Object_Manager_Interface $object_manager, array $extension_actions = [])
    {
        $this->object_manager = $object_manager;
        $this->actions = $extension_actions;
    }
    /**
     * @param string $entityType
     * @param string $actionName
     * @return object[]
     * @throws \Exception
     */
    public function get_actions($entity_type, $action_name)
    {
        $actions = [];
        foreach ($this->actions as $name => $action_group) {
            if (isset($action_group[$entity_type][$action_name])) {
                $actions[$name] = $this->object_manager->get($action_group[$entity_type][$action_name]);
            } elseif (isset($action_group['default'][$action_name])) {
                $actions[$name] = $this->object_manager->get($action_group['default'][$action_name]);
            }
        }
        return $actions;
    }
}