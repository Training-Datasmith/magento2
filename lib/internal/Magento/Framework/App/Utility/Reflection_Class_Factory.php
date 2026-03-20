<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Utility;

use ReflectionClass;
use Reflection_Exception;
/**
 * Factory for \ReflectionClass
 */
class Reflection_Class_Factory
{
    /**
     * Create a reflection class object
     *
     * @param object|string $objectOrClass
     *
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public function create($object_or_class): ReflectionClass
    {
        return new ReflectionClass($object_or_class);
    }
}