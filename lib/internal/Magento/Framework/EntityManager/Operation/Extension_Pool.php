<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation;

use Magento\Framework\Object_Manager_Interface;
/**
 * Class ExtensionPool
 */
class Extension_Pool
{
    /**
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @var object[]
     */
    protected $actions;
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
     * @return ExtensionInterface[]
     * @throws \Exception
     */
    public function get_actions($entity_type, $action_name)
    {
        $actions = [];
        if (!isset($this->actions[$entity_type][$action_name])) {
            return $actions;
        }
        foreach ($this->actions[$entity_type][$action_name] as $action_class_name) {
            $action = $this->object_manager->get($action_class_name);
            if (!$action instanceof Extension_Interface) {
                throw new \LogicException(get_class($action) . ' must implement ' . Extension_Interface::class);
            }
            $actions[] = $action;
        }
        return $actions;
    }
}