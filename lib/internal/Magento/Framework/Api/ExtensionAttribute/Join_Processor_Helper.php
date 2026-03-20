<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Extension_Attribute;

use Magento\Framework\Api\Extension_Attribute\Config\Converter;
use Magento\Framework\Api\Simple_Data_Object_Converter;
/**
 * Join processor helper class
 */
class Join_Processor_Helper
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\Config
     */
    private $config;
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinDataInterfaceFactory
     */
    private $join_data_interface_factory;
    /**
     * Initialize dependencies.
     *
     * @param Config $config
     * @param JoinDataInterfaceFactory $joinDataInterfaceFactory
     */
    public function __construct(Config $config, Join_Data_Interface_Factory $join_data_interface_factory)
    {
        $this->config = $config;
        $this->join_data_interface_factory = $join_data_interface_factory;
    }
    /**
     * Generate a list of select fields with mapping of client facing attribute names to field names used in SQL select.
     *
     * @param string $attributeCode
     * @param array $selectFields
     * @return array
     */
    public function get_select_fields_map($attribute_code, $select_fields)
    {
        $reference_table_alias = $this->get_reference_table_alias($attribute_code);
        $use_field_in_alias = count($select_fields) > 1;
        $select_fields_aliases = [];
        foreach ($select_fields as $select_field) {
            $internal_field_name = $select_field[Converter::JOIN_FIELD_COLUMN] ? $select_field[Converter::JOIN_FIELD_COLUMN] : $select_field[Converter::JOIN_FIELD];
            $setter_name = 'set' . ucfirst(Simple_Data_Object_Converter::snake_case_to_camel_case($select_field[Converter::JOIN_FIELD]));
            $select_fields_aliases[] = [Join_Data_Interface::SELECT_FIELD_EXTERNAL_ALIAS => $attribute_code . ($use_field_in_alias ? '.' . $select_field[Converter::JOIN_FIELD] : ''), Join_Data_Interface::SELECT_FIELD_INTERNAL_ALIAS => $reference_table_alias . '_' . $internal_field_name, Join_Data_Interface::SELECT_FIELD_WITH_DB_PREFIX => $reference_table_alias . '.' . $internal_field_name, Join_Data_Interface::SELECT_FIELD_SETTER => $setter_name];
        }
        return $select_fields_aliases;
    }
    /**
     * Generate reference table alias.
     *
     * @param string $attributeCode
     * @return string
     */
    public function get_reference_table_alias($attribute_code)
    {
        return 'extension_attribute_' . $attribute_code;
    }
    /**
     * Returns config data values
     *
     * @return array|mixed|null
     */
    public function get_config_data()
    {
        return $this->config->get();
    }
    /**
     * JoinDataInterface getter
     *
     * @return JoinDataInterface
     */
    public function get_join_data_interface()
    {
        return $this->join_data_interface_factory->create();
    }
}