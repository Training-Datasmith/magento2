<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Extension_Attribute;

use Magento\Framework\Api\Extensible_Data_Interface;
use Magento\Framework\Api\Extension_Attribute\Config\Converter;
use Magento\Framework\Api\Extension_Attributes_Factory;
use Magento\Framework\Data\Collection\Abstract_Db as DbCollection;
use Magento\Framework\Reflection\Type_Processor;
/**
 * Join processor allows to join extension attributes during collections loading.
 */
class Join_Processor implements \Magento\Framework\Api\Extension_Attribute\Join_Processor_Interface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    private $type_processor;
    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extension_attributes_factory;
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorHelper
     */
    private $join_processor_helper;
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param TypeProcessor $typeProcessor
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param JoinProcessorHelper $joinProcessorHelper
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, Type_Processor $type_processor, Extension_Attributes_Factory $extension_attributes_factory, Join_Processor_Helper $join_processor_helper)
    {
        $this->object_manager = $object_manager;
        $this->type_processor = $type_processor;
        $this->extension_attributes_factory = $extension_attributes_factory;
        $this->join_processor_helper = $join_processor_helper;
    }
    /**
     * @inheritdoc
     */
    public function process(Db_Collection $collection, $extensible_entity_class = null)
    {
        $extensible_entity_class = $extensible_entity_class ?: $collection->get_item_object_class();
        $join_directives = $this->get_join_directives_for_type($extensible_entity_class);
        foreach ($join_directives as $attribute_code => $directive) {
            /** @var JoinDataInterface $joinData */
            $join_data = $this->join_processor_helper->get_join_data_interface();
            $join_data->set_attribute_code($attribute_code)->set_reference_table($directive[Converter::JOIN_REFERENCE_TABLE])->set_reference_table_alias($this->get_reference_table_alias($attribute_code))->set_reference_field($directive[Converter::JOIN_REFERENCE_FIELD])->set_join_field($directive[Converter::JOIN_ON_FIELD]);
            $join_data->set_select_fields($this->join_processor_helper->get_select_fields_map($attribute_code, $directive[Converter::JOIN_FIELDS]));
            $collection->join_extension_attribute($join_data, $this);
        }
    }
    /**
     * Generate reference table alias.
     *
     * @param string $attributeCode
     * @return string
     */
    private function get_reference_table_alias($attribute_code)
    {
        return 'extension_attribute_' . $attribute_code;
    }
    /**
     * @inheritdoc
     */
    public function extract_extension_attributes($extensible_entity_class, array $data)
    {
        if (!$this->is_extensible_attributes_implemented($extensible_entity_class)) {
            /* do nothing as there are no extension attributes */
            return $data;
        }
        $join_directives = $this->get_join_directives_for_type($extensible_entity_class);
        $extension_data = [];
        foreach ($join_directives as $attribute_code => $directive) {
            $this->populate_attribute_code_with_directive($attribute_code, $directive, $data, $extension_data, $extensible_entity_class);
        }
        if (!empty($extension_data)) {
            $extension_attributes = $this->extension_attributes_factory->create($extensible_entity_class, $extension_data);
            $data[Extensible_Data_Interface::EXTENSION_ATTRIBUTES_KEY] = $extension_attributes;
        }
        return $data;
    }
    /**
     * Populate a specific attribute code with join directive instructions.
     *
     * @param string $attributeCode
     * @param array $directive
     * @param array $data
     * @param array $extensionData
     * @param string $extensibleEntityClass
     */
    private function populate_attribute_code_with_directive($attribute_code, $directive, &$data, &$extension_data, $extensible_entity_class)
    {
        $attribute_type = $directive[Converter::DATA_TYPE];
        $select_fields = $this->join_processor_helper->get_select_fields_map($attribute_code, $directive[Converter::JOIN_FIELDS]);
        foreach ($select_fields as $select_field) {
            $internal_alias = $select_field[Join_Data_Interface::SELECT_FIELD_INTERNAL_ALIAS];
            if (isset($data[$internal_alias])) {
                if ($this->type_processor->is_array_type($attribute_type)) {
                    throw new \LogicException(sprintf('Join directives cannot be processed for attribute (%s) of extensible entity (%s),' . ' which has an Array type (%s).', $attribute_code, $this->extension_attributes_factory->get_extensible_interface_name($extensible_entity_class), $attribute_type));
                } elseif ($this->type_processor->is_type_simple($attribute_type)) {
                    $extension_data['data'][$attribute_code] = $data[$internal_alias];
                    unset($data[$internal_alias]);
                    break;
                } else {
                    if (!isset($extension_data['data'][$attribute_code])) {
                        $extension_data['data'][$attribute_code] = $this->object_manager->create($attribute_type);
                    }
                    $setter_name = $select_field[Join_Data_Interface::SELECT_FIELD_SETTER];
                    $extension_data['data'][$attribute_code]->{$setter_name}($data[$internal_alias]);
                    unset($data[$internal_alias]);
                }
            }
        }
    }
    /**
     * Returns the internal join directive config for a given type.
     *
     * Array returned has all of the \Magento\Framework\Api\ExtensionAttribute\Config\Converter JOIN* fields set.
     *
     * @param string $extensibleEntityClass
     * @return array
     */
    private function get_join_directives_for_type($extensible_entity_class)
    {
        $extensible_interface_name = $this->extension_attributes_factory->get_extensible_interface_name($extensible_entity_class);
        $extensible_interface_name = ltrim($extensible_interface_name, '\\');
        $config = $this->join_processor_helper->get_config_data();
        if (!isset($config[$extensible_interface_name])) {
            return [];
        }
        $type_attributes_config = $config[$extensible_interface_name];
        $join_directives = [];
        foreach ($type_attributes_config as $attribute_code => $attribute_config) {
            if (isset($attribute_config[Converter::JOIN_DIRECTIVE])) {
                $join_directives[$attribute_code] = $attribute_config[Converter::JOIN_DIRECTIVE];
                $join_directives[$attribute_code][Converter::DATA_TYPE] = $attribute_config[Converter::DATA_TYPE];
            }
        }
        return $join_directives;
    }
    /**
     * Determine if the type is an actual extensible data interface.
     *
     * @param string $typeName
     * @return bool
     */
    private function is_extensible_attributes_implemented($type_name)
    {
        try {
            $this->extension_attributes_factory->get_extensible_interface_name($type_name);
            return true;
        } catch (\LogicException $e) {
            return false;
        }
    }
}