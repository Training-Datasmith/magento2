<?php

declare (strict_types=1);
/**
 * No route handlers retriever
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Router;

class No_Route_Handler_List
{
    /**
     * No route handlers instances
     *
     * @var NoRouteHandlerInterface[]
     */
    protected $_handlers;
    /**
     * @var array
     */
    protected $_handler_list;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $handlerClassesList
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, array $handler_classes_list)
    {
        $this->_handler_list = $handler_classes_list;
        $this->_object_manager = $object_manager;
    }
    /**
     * Get noRoute handlers
     *
     * @return NoRouteHandlerInterface[]
     */
    public function get_handlers()
    {
        if (!$this->_handlers) {
            //sorting handlers list
            $sorted_handlers_list = [];
            foreach ($this->_handler_list as $handler_info) {
                if (isset($handler_info['class']) && isset($handler_info['sortOrder'])) {
                    $sorted_handlers_list[$handler_info['class']] = $handler_info['sortOrder'];
                }
            }
            asort($sorted_handlers_list);
            //creating handlers
            foreach (array_keys($sorted_handlers_list) as $handler_instance) {
                $this->_handlers[] = $this->_object_manager->create($handler_instance);
            }
        }
        return $this->_handlers;
    }
}