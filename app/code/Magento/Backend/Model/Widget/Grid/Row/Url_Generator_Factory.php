<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Widget\Grid\Row;

/**
 * Grid row url generator factory
 *
 * @api
 * @since 100.0.2
 */
class Url_Generator_Factory
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
     * Create new url generator instance
     *
     * @param string $generatorClassName
     * @param array $arguments
     * @return \Magento\Backend\Model\Widget\Grid\Row\UrlGenerator
     * @throws \InvalidArgumentException
     */
    public function create_url_generator($generator_class_name, array $arguments = [])
    {
        $row_url_generator = $this->_object_manager->create($generator_class_name, $arguments);
        if (false === $row_url_generator instanceof \Magento\Backend\Model\Widget\Grid\Row\Generator_Interface) {
            throw new \InvalidArgumentException('Passed wrong parameters');
        }
        return $row_url_generator;
    }
}