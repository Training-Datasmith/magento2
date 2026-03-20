<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

/**
 * Resolves types.
 */
class Type_Resolver
{
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var array
     */
    private $type_mapping = [
        \Magento\Sales_Rule\Model\Rule::class => \Magento\Sales_Rule\Api\Data\Rule_Interface::class,
        // phpstan:ignore "Class Magento\SalesRule\Model\Rule\Interceptor not found."
        \Magento\Sales_Rule\Model\Rule\Interceptor::class => \Magento\Sales_Rule\Api\Data\Rule_Interface::class,
        // phpstan:ignore "Class Magento\SalesRule\Model\Rule\Proxy not found."
        \Magento\Sales_Rule\Model\Rule\Proxy::class => \Magento\Sales_Rule\Api\Data\Rule_Interface::class,
    ];
    /**
     * TypeResolver constructor.
     * @param MetadataPool $metadataPool
     */
    public function __construct(Metadata_Pool $metadata_pool)
    {
        $this->metadata_pool = $metadata_pool;
    }
    /**
     * Resolves type.
     *
     * @param object $type
     * @return string
     * @throws \Exception
     */
    public function resolve($type)
    {
        // @todo remove after MAGETWO-52608 resolved
        $class_name = get_class($type);
        if (isset($this->type_mapping[$class_name])) {
            return $this->type_mapping[$class_name];
        }
        $reflection_class = new \ReflectionClass($type);
        $interface_names = $reflection_class->get_interface_names();
        $data_interfaces = [];
        foreach ($interface_names as $interface_name) {
            if ($interface_name && strpos($interface_name, '\Api\Data\\') !== false) {
                $data_interfaces[] = $interface_name;
            }
        }
        if (count($data_interfaces) == 0) {
            return $class_name;
        }
        foreach ($data_interfaces as $data_interface) {
            if ($this->metadata_pool->has_configuration($data_interface)) {
                $this->type_mapping[$class_name] = $data_interface;
            }
        }
        if (empty($this->type_mapping[$class_name])) {
            $this->type_mapping[$class_name] = reset($data_interfaces);
        }
        return $this->type_mapping[$class_name];
    }
}