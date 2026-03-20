<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Generator;

use Magento\Framework\Autoload\Autoloader_Registry;
/**
 * DefinedClasses class detects if a class has been defined
 */
class Defined_Classes
{
    /**
     * Determine if a class can be loaded without using Code\Generator\Autoloader.
     *
     * @param string $className
     * @return bool
     */
    public function is_class_loadable($class_name)
    {
        return $this->is_class_loadable_from_memory($class_name) || $this->is_class_loadable_from_disk($class_name);
    }
    /**
     * Determine if a class exists in memory
     *
     * @param string $className
     * @return bool
     */
    public function is_class_loadable_from_memory($class_name)
    {
        return class_exists($class_name, false) || interface_exists($class_name, false);
    }
    /**
     * Determine if a class exists on disk
     *
     * @param string $className
     * @return bool
     * @deprecated 102.0.0
     */
    public function is_class_loadable_from_disc($class_name)
    {
        return $this->is_class_loadable_from_disk($class_name);
    }
    /**
     * Determine if a class exists on disk
     *
     * @param string $className
     * @return bool
     */
    public function is_class_loadable_from_disk($class_name)
    {
        try {
            return (bool) Autoloader_Registry::get_autoloader()->find_file($class_name);
        } catch (\Exception $e) {
            // Couldn't get access to the autoloader so we need to allow class_exists to call autoloader chain
            return class_exists($class_name) || interface_exists($class_name);
        }
    }
}