<?php

declare (strict_types=1);
/**
 * Application language config factory
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Language;

/**
 * @codeCoverageIgnore
 */
class Config_Factory
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
     * Create config
     *
     * @param array $arguments
     * @return Config
     */
    public function create(array $arguments = [])
    {
        return $this->_object_manager->create(\Magento\Framework\App\Language\Config::class, $arguments);
    }
}