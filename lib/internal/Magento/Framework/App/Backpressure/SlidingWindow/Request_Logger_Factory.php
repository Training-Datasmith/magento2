<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure\Sliding_Window;

use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Object_Manager_Interface;
/**
 * Creates Backpressure Logger by type
 */
class Request_Logger_Factory implements Request_Logger_Factory_Interface
{
    /**
     * @var ObjectManagerInterface
     */
    private Object_Manager_Interface $object_manager;
    /**
     * @var array
     */
    private array $types;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $types
     */
    public function __construct(Object_Manager_Interface $object_manager, array $types)
    {
        $this->types = $types;
        $this->object_manager = $object_manager;
    }
    /**
     * @inheritDoc
     *
     * @param string $type
     * @return RequestLoggerInterface
     * @throws RuntimeException
     */
    public function create(string $type): Request_Logger_Interface
    {
        if (isset($this->types[$type])) {
            return $this->object_manager->create($this->types[$type]);
        }
        throw new RuntimeException(__('Invalid request logger type: %1', $type));
    }
}