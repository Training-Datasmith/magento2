<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\Object_Manager\Factory_Interface;
/**
 * Direct usage of this class is strictly discouraged.
 *
 * Wrapper around object manager with workarounds to access it in client code.
 * Provides static access to objectManager, that is required for unserialization of objects.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Object_Manager extends \Magento\Framework\Object_Manager\Object_Manager
{
    /**
     * @var ObjectManager
     */
    protected static $_instance;
    /**
     * Retrieve object manager
     *
     * @return ObjectManager
     * @throws \RuntimeException
     */
    public static function get_instance()
    {
        if (!self::$_instance instanceof \Magento\Framework\Object_Manager_Interface) {
            throw new \RuntimeException('ObjectManager isn\'t initialized');
        }
        return self::$_instance;
    }
    /**
     * Set object manager instance
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @throws \LogicException
     * @return void
     */
    public static function set_instance(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        self::$_instance = $object_manager;
    }
    /**
     * @param FactoryInterface $factory
     * @param \Magento\Framework\ObjectManager\ConfigInterface $config
     * @param array $sharedInstances
     */
    public function __construct(Factory_Interface $factory, \Magento\Framework\Object_Manager\Config_Interface $config, array &$shared_instances = [])
    {
        parent::__construct($factory, $config, $shared_instances);
        self::$_instance = $this;
    }
}