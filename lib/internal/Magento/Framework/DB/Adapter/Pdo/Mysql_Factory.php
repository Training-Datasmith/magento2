<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Adapter\Pdo;

use Magento\Framework\DB\Logger_Interface;
use Magento\Framework\DB\Select_Factory;
use Magento\Framework\Object_Manager_Interface;
/**
 * Factory for Mysql adapter
 *
 * @api
 */
class Mysql_Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create instance of Mysql adapter
     *
     * @param string $className
     * @param array $config
     * @param LoggerInterface|null $logger
     * @param SelectFactory|null $selectFactory
     * @return Mysql
     * @throws \InvalidArgumentException
     */
    public function create($class_name, array $config, ?Logger_Interface $logger = null, ?Select_Factory $select_factory = null)
    {
        if (!in_array(Mysql::class, class_parents($class_name, true) + [$class_name => $class_name])) {
            throw new \InvalidArgumentException('Invalid class, ' . $class_name . ' must extend ' . Mysql::class . '.');
        }
        $arguments = ['config' => $config];
        if ($logger) {
            $arguments['logger'] = $logger;
        }
        if ($select_factory) {
            $arguments['selectFactory'] = $select_factory;
        }
        return $this->object_manager->create($class_name, $arguments);
    }
}