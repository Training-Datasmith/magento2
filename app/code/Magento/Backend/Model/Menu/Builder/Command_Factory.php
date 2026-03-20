<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Builder;

/**
 * Menu builder command factory
 * @api
 * @since 100.0.2
 */
class Command_Factory
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
     * Create new command object
     *
     * @param string $commandName
     * @param array $data
     * @return \Magento\Config\Model\Config
     */
    public function create($command_name, array $data = [])
    {
        return $this->_object_manager->create('Magento\Backend\Model\Menu\Builder\Command\\' . ucfirst($command_name), $data);
    }
}