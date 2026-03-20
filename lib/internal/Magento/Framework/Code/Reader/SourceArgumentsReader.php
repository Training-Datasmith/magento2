<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Reader;

use Magento\Framework\Get_Parameter_Class_Trait;
class Source_Arguments_Reader
{
    use Get_Parameter_Class_Trait;
    /**
     * Namespace separator
     * @deprecated
     * @see \Magento\Framework\Code\Reader\NamespaceResolver::NS_SEPARATOR
     */
    public const NS_SEPARATOR = '\\';
    /**
     * @var NamespaceResolver
     */
    private $namespace_resolver;
    /**
     * @param NamespaceResolver|null $namespaceResolver
     */
    public function __construct(?Namespace_Resolver $namespace_resolver = null)
    {
        $this->namespace_resolver = $namespace_resolver ?: new Namespace_Resolver();
    }
    /**
     * Read constructor argument types from source code and perform namespace resolution if required.
     *
     * @param \ReflectionClass $class
     * @param bool $inherited
     * @return array List of constructor argument types.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function get_constructor_argument_types(\ReflectionClass $class, $inherited = false)
    {
        $output = [null];
        if (!$class->get_file_name() || false == $class->has_method('__construct') || !$inherited && $class->get_constructor()->class !== $class->get_name()) {
            return $output;
        }
        //Reading parameters' types.
        $params = $class->get_constructor()->get_parameters();
        /** @var string[] $types */
        $types = [];
        foreach ($params as $param) {
            //For the sake of backward compatibility.
            $type_name = '';
            $parameter_type = $param->get_type();
            if ($parameter_type && $parameter_type->get_name() === 'array') {
                //For the sake of backward compatibility.
                $type_name = 'array';
            } else {
                try {
                    $param_class = $this->get_parameter_class($param);
                    if ($param_class) {
                        $type_name = '\\' . $param_class->get_name();
                    }
                } catch (\Reflection_Exception $exception) {
                    //If there's a problem loading a class then ignore it and
                    //just return it's name.
                    $type_name = '\\' . $parameter_type->get_name();
                }
            }
            $types[] = $type_name;
        }
        if (!$types) {
            //For the sake of backward compatibility.
            $types = [null];
        }
        return $types;
    }
    /**
     * Perform namespace resolution if required and return fully qualified name.
     *
     * @param string $argument
     * @param array $availableNamespaces
     * @return string
     * @deprecated 101.0.0
     * @see getConstructorArgumentTypes
     */
    protected function resolve_namespaces($argument, $available_namespaces)
    {
        return $this->namespace_resolver->resolve_namespace($argument, $available_namespaces);
    }
    /**
     * Remove default value from argument.
     *
     * @param string $argument
     * @param string $token
     * @return string
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    protected function remove_token($argument, $token)
    {
        $position = strpos($argument, $token);
        if (is_numeric($position)) {
            return substr($argument, 0, $position);
        }
        return $argument;
    }
    /**
     * Get all imported namespaces.
     *
     * @param array $file
     * @return array
     * @deprecated 101.0.0
     * @see getConstructorArgumentTypes
     */
    protected function get_imported_namespaces(array $file)
    {
        return $this->namespace_resolver->get_imported_namespaces($file);
    }
}