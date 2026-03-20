<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Async\Code\Generator;

use Magento\Framework\Async\Deferred_Interface;
use Magento\Framework\Code\Generator\Entity_Abstract;
use Magento\Framework\Get_Reflection_Method_Return_Type_Value_Trait;
use Magento\Framework\Object_Manager\Definition_Factory;
use Magento\Framework\Object_Manager\Noninterceptable_Interface;
/**
 * Generator for proxies for late values resolving.
 */
class Proxy_Deferred_Generator extends Entity_Abstract
{
    use Get_Reflection_Method_Return_Type_Value_Trait;
    /**
     * Entity type
     */
    public const ENTITY_TYPE = 'proxyDeferred';
    /**
     * @inheritDoc
     */
    protected function _get_default_result_class_name($model_class_name)
    {
        return $model_class_name . '_' . ucfirst(static::ENTITY_TYPE);
    }
    /**
     * @inheritDoc
     */
    protected function _get_class_properties()
    {
        $properties[] = ['name' => 'instance', 'visibility' => 'private', 'docblock' => ['shortDescription' => 'Proxied instance', 'tags' => [['name' => 'var', 'description' => 'string']]]];
        $properties[] = ['name' => 'deferred', 'visibility' => 'private', 'docblock' => ['shortDescription' => 'Deferred to wait for', 'tags' => [['name' => 'var', 'description' => 'string']]]];
        return $properties;
    }
    /**
     * @inheritDoc
     */
    protected function _get_class_methods()
    {
        $construct = $this->_get_default_constructor_definition();
        $source_class_name = $this->get_source_class_name();
        // create proxy methods for all non-static and non-final public methods (excluding constructor)
        $methods = [$construct];
        //Only serializing the result.
        $methods[] = ['name' => '__sleep', 'body' => "\$this->wait();\nreturn ['instance'];", 'docblock' => ['shortDescription' => 'Serialize only the instance', 'tags' => [['name' => 'return', 'description' => 'array']]]];
        //Only cloning the result.
        $methods[] = ['name' => '__clone', 'body' => "\$this->wait();\n\$this->instance = clone \$this->instance;", 'docblock' => ['shortDescription' => 'Clone proxied instance']];
        //Getting deferred value.
        $methods[] = ['name' => 'wait', 'visibility' => 'private', 'body' => "if (!\$this->instance) {\n" . "    \$this->instance = \$this->deferred->get();\n" . "    if (!\$this->instance instanceof {$source_class_name}) {\n" . "        throw new \\RuntimeException('Wrong instance returned by deferred');\n" . "    }\n" . "}\n" . 'return $this->instance;', 'docblock' => ['shortDescription' => 'Get proxied instance', 'tags' => [['name' => 'return', 'description' => $source_class_name]]]];
        $reflection_class = new \ReflectionClass($source_class_name);
        $public_methods = $reflection_class->get_methods(\ReflectionMethod::IS_PUBLIC);
        foreach ($public_methods as $method) {
            if (!($method->is_constructor() || $method->is_final() || $method->is_static() || $method->is_destructor()) && !in_array($method->get_name(), ['__sleep', '__wakeup', '__clone'])) {
                $methods[] = $this->_get_method_info($method);
            }
        }
        return $methods;
    }
    /**
     * @inheritDoc
     */
    protected function _generate_code()
    {
        $type_name = $this->get_source_class_name();
        $reflection = new \ReflectionClass($type_name);
        if ($reflection->is_interface()) {
            $this->_class_generator->set_implemented_interfaces([$type_name, '\\' . Noninterceptable_Interface::class]);
        } else {
            $this->_class_generator->set_extended_class($type_name);
            $this->_class_generator->set_implemented_interfaces(['\\' . Noninterceptable_Interface::class]);
        }
        return parent::_generate_code();
    }
    /**
     * Collect method info
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    protected function _get_method_info(\ReflectionMethod $method)
    {
        $parameter_names = [];
        $parameters = [];
        foreach ($method->get_parameters() as $parameter) {
            $name = $parameter->is_variadic() ? '... $' . $parameter->get_name() : '$' . $parameter->get_name();
            $parameter_names[] = $name;
            $parameters[] = $this->_get_method_parameter_info($parameter);
        }
        $return_type_value = $this->get_return_type_value($method);
        $method_info = ['name' => $method->get_name(), 'parameters' => $parameters, 'body' => $this->_get_method_body($method->get_name(), $parameter_names, $return_type_value === 'void'), 'docblock' => ['shortDescription' => '@inheritDoc'], 'returntype' => $return_type_value];
        return $method_info;
    }
    /**
     * @inheritDoc
     */
    protected function _get_default_constructor_definition()
    {
        return ['name' => '__construct', 'parameters' => [['name' => 'deferred', 'type' => '\\' . Deferred_Interface::class]], 'body' => '$this->deferred = $deferred;', 'docblock' => ['shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor', 'tags' => [['name' => 'param', 'description' => '\\' . Definition_Factory::class . ' $objectManager']]]];
    }
    /**
     * Build proxy method body
     *
     * @param string $name
     * @param array $parameters
     * @param bool $withoutReturn
     * @return string
     */
    protected function _get_method_body($name, array $parameters = [], bool $without_return = false)
    {
        if (count($parameters) == 0) {
            $method_call = sprintf('%s()', $name);
        } else {
            $method_call = sprintf('%s(%s)', $name, implode(', ', $parameters));
        }
        //Waiting for deferred result and using it's methods.
        return "\$this->wait();\n" . ($without_return ? '' : 'return ') . "\$this->instance->{$method_call};";
    }
    /**
     * @inheritDoc
     */
    protected function _validate_data()
    {
        $result = parent::_validate_data();
        if ($result) {
            $source_class_name = $this->get_source_class_name();
            $result_class_name = $this->_get_result_class_name();
            if ($result_class_name !== $source_class_name . '\ProxyDeferred') {
                $this->_add_error('Invalid ProxyDeferred class name [' . $result_class_name . ']. Use ' . $source_class_name . '\ProxyDeferred');
                $result = false;
            }
        }
        return $result;
    }
}