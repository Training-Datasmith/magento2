<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config\Data;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Object_Manager_Interface;
/**
 * Factory for ConfigData.
 *
 * @api
 */
class Config_Data_Factory
{
    /**
     * @var ObjectManager
     */
    private $object_manager;
    /**
     * Factory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Returns a new instance of ConfigData on every call.
     *
     * @param string $fileKey
     * @return ConfigData
     */
    public function create($file_key)
    {
        return $this->object_manager->create(Config_Data::class, ['fileKey' => $file_key]);
    }
}