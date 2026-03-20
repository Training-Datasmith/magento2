<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * Magento configuration DOM factory
 * @api
 * @since 100.0.2
 */
class Dom_Factory
{
    public const CLASS_NAME = \Magento\Framework\Config\Dom::class;
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManger
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manger)
    {
        $this->_object_manager = $object_manger;
    }
    /**
     * Create DOM object
     *
     * @param array $arguments
     * @return \Magento\Framework\Config\Dom
     */
    public function create_dom(array $arguments = [])
    {
        return $this->_object_manager->create(self::CLASS_NAME, $arguments);
    }
}