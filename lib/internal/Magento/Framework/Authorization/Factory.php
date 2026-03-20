<?php

declare (strict_types=1);
/**
 * Factory class for \Magento\Framework\Authorization
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Authorization;

use Magento\Framework\Authorization;
use Magento\Framework\Object_Manager_Interface;
class Factory
{
    /**
     * Entity class name
     */
    public const CLASS_NAME = \Magento\Framework\Authorization::class;
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $_object_manager = null;
    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return Authorization
     */
    public function create(array $data = [])
    {
        return $this->_object_manager->create(self::CLASS_NAME, $data);
    }
}