<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Reader;

use Magento\Framework\Get_Parameter_Class_Trait;
use ReflectionClass;
use Reflection_Exception;
use ReflectionParameter;
/**
 * Class ClassReader
 */
class Class_Reader implements Class_Reader_Interface
{
    use Get_Parameter_Class_Trait;
    /**
     * @var array
     */
    private $parents_cache = [];
    /**
     * Read class constructor signature
     *
     * @param  string $className
     * @return array|null
     * @throws ReflectionException
     */
    public function get_constructor($class_name)
    {
        $class = new ReflectionClass($class_name);
        $result = null;
        $constructor = $class->get_constructor();
        if ($constructor) {
            $result = [];
            /** @var $parameter ReflectionParameter */
            foreach ($constructor->get_parameters() as $parameter) {
                try {
                    $parameter_class = $this->get_parameter_class($parameter);
                    $result[] = [$parameter->get_name(), $parameter_class ? $parameter_class->get_name() : null, !$parameter->is_optional() && !$parameter->is_default_value_available(), $this->get_reflection_parameter_default_value($parameter), $parameter->is_variadic()];
                } catch (Reflection_Exception $e) {
                    $message = sprintf('Impossible to process constructor argument %s of %s class', $parameter->__toString(), $class_name);
                    throw new Reflection_Exception($message, 0, $e);
                }
            }
        }
        return $result;
    }
    /**
     * Get reflection parameter default value
     *
     * @param  ReflectionParameter $parameter
     * @return array|mixed|null
     */
    private function get_reflection_parameter_default_value(ReflectionParameter $parameter)
    {
        if ($parameter->is_variadic()) {
            return [];
        }
        return $parameter->is_default_value_available() ? $parameter->get_default_value() : null;
    }
    /**
     * Retrieve parent relation information for type in a following format
     * array(
     *     'Parent_Class_Name',
     *     'Interface_1',
     *     'Interface_2',
     *     ...
     * )
     *
     * @param  string $className
     * @return string[]
     */
    public function get_parents($class_name)
    {
        if (isset($this->parents_cache[$class_name])) {
            return $this->parents_cache[$class_name];
        }
        $parent_class = get_parent_class($class_name);
        if ($parent_class) {
            $result = [];
            $interfaces = class_implements($class_name);
            if ($interfaces) {
                $parent_interfaces = class_implements($parent_class);
                if ($parent_interfaces) {
                    $result = array_values(array_diff($interfaces, $parent_interfaces));
                } else {
                    $result = array_values($interfaces);
                }
            }
            array_unshift($result, $parent_class);
        } else {
            $result = array_values(class_implements($class_name));
            if ($result) {
                array_unshift($result, null);
            } else {
                $result = [];
            }
        }
        $this->parents_cache[$class_name] = $result;
        return $result;
    }
    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}