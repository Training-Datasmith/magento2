<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Factory class for instantiation of extension attributes objects.
 */
class Extension_Attributes_Factory
{
    public const EXTENSIBLE_INTERFACE_NAME = \Magento\Framework\Api\Extensible_Data_Interface::class;
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * Map is used for performance optimization.
     *
     * @var array
     */
    private $class_interface_map = [];
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create extension attributes object, custom for each extensible class.
     *
     * @param string $extensibleClassName
     * @param array $data
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    public function create($extensible_class_name, $data = [])
    {
        $interface_reflection = new \ReflectionClass($this->get_extensible_interface_name($extensible_class_name));
        $method_reflection = $interface_reflection->get_method('getExtensionAttributes');
        if ($method_reflection->get_declaring_class()->get_name() === self::EXTENSIBLE_INTERFACE_NAME) {
            throw new \LogicException("Method 'getExtensionAttributes' must be overridden in the interfaces " . "which extend '" . self::EXTENSIBLE_INTERFACE_NAME . "'. " . 'Concrete return type should be specified.');
        }
        $interface_name = '\\' . $interface_reflection->get_name();
        $extension_class_name = substr($interface_name, 0, -strlen('Interface')) . 'Extension';
        $extension_interface_name = $extension_class_name . 'Interface';
        /** Ensure that proper return type of getExtensionAttributes() method is specified */
        $method_doc_block = $method_reflection->get_doc_comment();
        $pattern = "/@return\\s+" . str_replace('\\', '\\\\', $extension_interface_name) . '/';
        if (!preg_match($pattern, $method_doc_block)) {
            throw new \LogicException("Method 'getExtensionAttributes' must be overridden in the interfaces " . "which extend '" . self::EXTENSIBLE_INTERFACE_NAME . "'. " . 'Concrete return type must be specified. Please fix :' . $interface_name);
        }
        $extension_factory_name = $extension_class_name . 'Factory';
        $extension_factory = $this->object_manager->create($extension_factory_name);
        return $extension_factory->create($data);
    }
    /**
     * Identify concrete extensible interface name based on the class name.
     *
     * @param string $extensibleClassName
     * @return string
     */
    public function get_extensible_interface_name($extensible_class_name)
    {
        $exception_message = "Class '{$extensible_class_name}' must implement an interface, " . "which extends from '" . self::EXTENSIBLE_INTERFACE_NAME . "'";
        $not_extensible_class_flag = '';
        if (isset($this->class_interface_map[$extensible_class_name])) {
            if ($not_extensible_class_flag === $this->class_interface_map[$extensible_class_name]) {
                throw new \LogicException($exception_message);
            } else {
                return $this->class_interface_map[$extensible_class_name];
            }
        }
        $model_reflection = new \ReflectionClass($extensible_class_name);
        if ($model_reflection->is_interface() && $model_reflection->is_subclass_of(self::EXTENSIBLE_INTERFACE_NAME) && $model_reflection->has_method('getExtensionAttributes')) {
            $this->class_interface_map[$extensible_class_name] = $extensible_class_name;
            return $this->class_interface_map[$extensible_class_name];
        }
        foreach ($model_reflection->get_interfaces() as $interface_reflection) {
            if ($interface_reflection->is_subclass_of(self::EXTENSIBLE_INTERFACE_NAME) && $interface_reflection->has_method('getExtensionAttributes')) {
                $this->class_interface_map[$extensible_class_name] = $interface_reflection->get_name();
                return $this->class_interface_map[$extensible_class_name];
            }
        }
        $this->class_interface_map[$extensible_class_name] = $not_extensible_class_flag;
        throw new \LogicException($exception_message);
    }
}