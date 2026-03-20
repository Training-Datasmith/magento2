<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Object_Manager_Interface;
/**
 * Factory for report providers
 */
class Provider_Factory
{
    public function __construct(private readonly Object_Manager_Interface $object_manager)
    {
    }
    /**
     * Object creation
     *
     * @param string $providerName
     * @return object
     */
    public function create($provider_name)
    {
        return $this->object_manager->get($provider_name);
    }
}