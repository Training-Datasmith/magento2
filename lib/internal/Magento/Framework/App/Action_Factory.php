<?php

declare (strict_types=1);
/**
 * Action Factory
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * @api
 * @since 100.0.2
 */
class Action_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Create action
     *
     * @param string $actionName
     * @return ActionInterface
     * @throws \InvalidArgumentException
     */
    public function create($action_name)
    {
        if (!is_subclass_of($action_name, \Magento\Framework\App\Action_Interface::class)) {
            throw new \InvalidArgumentException('The action name provided is invalid. Verify the action name and try again.');
        }
        return $this->_object_manager->create($action_name);
    }
}