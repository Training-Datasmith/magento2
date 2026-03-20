<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Reader;

use Laminas\Code\Reflection\Parameter_Reflection;
use Magento\Framework\Get_Parameter_Class_Trait;
/**
 * The class arguments reader
 */
class Arguments_Reader extends Parameter_Reflection
{
    use Get_Parameter_Class_Trait;
    public const NO_DEFAULT_VALUE = 'NO-DEFAULT';
    /**
     * @var NamespaceResolver
     */
    private $namespace_resolver;
    /**
     * @var ScalarTypesProvider
     */
    private $scalar_types_provider;
    /**
     * @var ParameterReflection
     */
    protected $parameter_reflection;
    /**
     * @param NamespaceResolver|null $namespaceResolver
     * @param ScalarTypesProvider|null $scalarTypesProvider
     */
    public function __construct(?Namespace_Resolver $namespace_resolver = null, ?Scalar_Types_Provider $scalar_types_provider = null)
    {
        $this->namespace_resolver = $namespace_resolver ?: new Namespace_Resolver();
        $this->scalar_types_provider = $scalar_types_provider ?: new Scalar_Types_Provider();
    }
    /**
     * Get class constructor
     *
     * @param \ReflectionClass $class
     * @param bool $groupByPosition
     * @param bool $inherited
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function get_constructor_arguments(\ReflectionClass $class, $group_by_position = false, $inherited = false)
    {
        $output = [];
        /**
         * Skip native PHP types, classes without constructor
         */
        if ($class->is_interface() || !$class->get_file_name() || false == $class->has_method('__construct') || !$inherited && $class->get_constructor()->class != $class->get_name()) {
            return $output;
        }
        $constructor = new \Laminas\Code\Reflection\Method_Reflection($class->get_name(), '__construct');
        foreach ($constructor->get_parameters() as $parameter) {
            $name = $parameter->get_name();
            $position = $parameter->get_position();
            $index = $group_by_position ? $position : $name;
            $default = null;
            if ($parameter->is_optional()) {
                if ($parameter->is_default_value_available()) {
                    $value = $parameter->get_default_value();
                    if (true == is_array($value)) {
                        $default = $this->_var_export_min($value);
                    } elseif (true == is_int($value)) {
                        $default = $value;
                    } else {
                        $default = $parameter->get_default_value();
                    }
                } elseif ($parameter->allows_null()) {
                    $default = null;
                }
            }
            $output[$index] = ['name' => $name, 'position' => $position, 'type' => $this->process_type($class, $parameter), 'isOptional' => $parameter->is_optional(), 'default' => $default];
        }
        return $output;
    }
    /**
     * Process argument type.
     *
     * @param \ReflectionClass $class
     * @param \Laminas\Code\Reflection\ParameterReflection $parameter
     * @return string
     */
    private function process_type(\ReflectionClass $class, \Laminas\Code\Reflection\Parameter_Reflection $parameter)
    {
        $this->parameter_reflection = $parameter;
        $parameter_class = $this->get_parameter_class($parameter);
        if ($parameter_class) {
            return Namespace_Resolver::NS_SEPARATOR . $parameter_class->get_name();
        }
        $type = $this->detect_type();
        /**
         * $type === null if it is unspecified
         * $type === 'null' if it is used in doc block
         */
        if ($type === null || $type === 'null') {
            return null;
        }
        if (strpos($type, '[]') !== false) {
            return 'array';
        }
        if (!in_array($type, $this->scalar_types_provider->get_types())) {
            $available_namespaces = $this->namespace_resolver->get_imported_namespaces(file($class->get_file_name()));
            $available_namespaces[0] = $class->get_namespace_name();
            return $this->namespace_resolver->resolve_namespace($type, $available_namespaces);
        }
        return $type;
    }
    /**
     * Get arguments of parent __construct call
     *
     * @param \ReflectionClass $class
     * @param array $classArguments
     * @return array|null
     * @throws \ReflectionException
     */
    public function get_parent_call(\ReflectionClass $class, array $class_arguments): ?array
    {
        /** Skip native PHP types */
        if (!$class->get_file_name()) {
            return null;
        }
        $trim_function = function (&$value) {
            $position = strpos($value, ':');
            if ($position !== false) {
                $value = trim(substr($value, 0, $position), PHP_EOL . ' ');
            } else {
                $value = trim($value, PHP_EOL . ' $');
            }
        };
        $method = $class->get_method('__construct');
        $start = $method->get_start_line();
        $end = $method->get_end_line();
        $length = $end - $start;
        $source = file($class->get_file_name());
        $content = implode('', array_slice($source, $start, $length));
        $pattern = '/parent::__construct\(([ ' . PHP_EOL . ']*' . '([a-zA-Z0-9_]+([ ' . PHP_EOL . '])*:([ ' . PHP_EOL . '])*)*[$][a-zA-Z0-9_]*,)*[ ' . PHP_EOL . ']*' . '([a-zA-Z0-9_]+([ ' . PHP_EOL . '])*:([ ' . PHP_EOL . '])*)*([$][a-zA-Z0-9_]*)[' . PHP_EOL . ' ]*\);/';
        if (!preg_match($pattern, $content, $matches)) {
            return null;
        }
        $arguments = $matches[0];
        if (!trim($arguments)) {
            return null;
        }
        $arguments = substr(trim($arguments), 20, -2);
        $arguments = explode(',', $arguments);
        $is_named_argument = [];
        foreach ($arguments as $argument_position => $argument_name) {
            $is_named_argument[$argument_position] = (bool) strpos($argument_name, ':');
        }
        array_walk($arguments, $trim_function);
        $output = [];
        foreach ($arguments as $argument_position => $argument_name) {
            $type = isset($class_arguments[$argument_name]) ? $class_arguments[$argument_name]['type'] : null;
            $output[$argument_position] = ['name' => $argument_name, 'position' => $argument_position, 'type' => $type, 'isNamedArgument' => $is_named_argument[$argument_position]];
        }
        return $output;
    }
    /**
     * Check argument type compatibility
     *
     * @param string $requiredType
     * @param string $actualType
     * @return bool
     */
    public function is_compatible_type($required_type, $actual_type)
    {
        /** Types are compatible if type names are equal */
        if ($required_type === $actual_type) {
            return true;
        }
        /** Types are 'semi-compatible' if one of them are undefined */
        if ($required_type === null || $actual_type === null) {
            return true;
        }
        /**
         * Special case for scalar arguments
         * Array type is compatible with array or null type. Both of these types are checked above
         */
        if ($required_type === 'array' || $actual_type === 'array') {
            return false;
        }
        if ($required_type === 'mixed' || $actual_type === 'mixed') {
            return true;
        }
        return is_subclass_of($actual_type, $required_type);
    }
    /**
     * Export variable value
     *
     * @param mixed $var
     * @return mixed|string
     */
    protected function _var_export_min($var)
    {
        if (is_array($var)) {
            $to_implode = [];
            foreach ($var as $key => $value) {
                $to_implode[] = var_export($key, true) . ' => ' . $this->_var_export_min($value);
            }
            $code = 'array(' . implode(', ', $to_implode) . ')';
            return $code;
        } else {
            return var_export($var, true);
        }
    }
    /**
     * Get constructor annotations
     *
     * @param \ReflectionClass $class
     * @return array
     */
    public function get_annotations(\ReflectionClass $class)
    {
        $regexp = '(@([a-z_][a-z0-9_]+)\(([^\)]+)\))i';
        $doc_block = $class->get_constructor()->get_doc_comment();
        $annotations = [];
        preg_match_all($regexp, $doc_block, $matches);
        foreach (array_keys($matches[0]) as $index) {
            $name = $matches[1][$index];
            $value = trim($matches[2][$index], '" ');
            $annotations[$name] = $value;
        }
        return $annotations;
    }
    /**
     * ReflectionType does not have an isBuiltin() / getName() method
     *
     * @deprecated this method is unreliable, and should not be used: it will be removed in the next major release.
     *             It may crash on parameters with union types, and will return relative types, instead of
     *             FQN references
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @see reflectionnamedtype.isbuiltin.php
     *
     * @return mixed|string|void|null
     */
    public function detect_type()
    {
        if (null !== ($type = $this->parameter_reflection->get_type()) && method_exists($type, 'isBuiltin') && $type->is_builtin()) {
            return $type->get_name();
        }
        if (null !== $type && method_exists($type, 'getName') && $type->get_name() === 'self') {
            $declaring_class = $this->parameter_reflection->get_declaring_class();
            // @codingStandardsIgnoreStart
            assert($declaring_class !== null, 'A parameter called `self` can only exist on a class');
            // @codingStandardsIgnoreEnd
            return $declaring_class->get_name();
        }
        if (($class = $this->parameter_reflection->get_class()) instanceof \ReflectionClass) {
            return $class->get_name();
        }
        $doc_block = $this->parameter_reflection->get_declaring_function()->get_doc_block();
        if (!$doc_block instanceof \Laminas\Code\Reflection\Doc_Block_Reflection) {
            return null;
        }
        $params = $doc_block->get_tags('param');
        $param_tag = $params[$this->parameter_reflection->get_position()] ?? null;
        $variable_name = '$' . $this->parameter_reflection->get_name();
        if ($param_tag && ('' === $param_tag->get_variable_name() || $variable_name === $param_tag->get_variable_name())) {
            return $param_tag->get_types()[0] ?? '';
        }
        foreach ($params as $param) {
            if ($param->get_variable_name() === $variable_name) {
                return $param->get_types()[0] ?? '';
            }
        }
        return null;
    }
}