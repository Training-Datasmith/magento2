<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Async;

use Magento\Framework\Object_Manager_Interface;
/**
 * Create deferred proxy for a class.
 */
class Proxy_Deferred_Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create deferred proxy for given class.
     *
     * @param string $className
     * @param DeferredInterface $deferred
     * @return object Instance of $className.
     */
    public function create_for(string $class_name, Deferred_Interface $deferred)
    {
        return $this->object_manager->create($class_name . '\ProxyDeferred', ['deferred' => $deferred]);
    }
}