<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Extension_Attribute\Config;

class Converter implements \Magento\Framework\Config\Converter_Interface
{
    public const RESOURCE_PERMISSIONS = 'resourceRefs';
    public const DATA_TYPE = 'type';
    public const JOIN_DIRECTIVE = 'join';
    public const JOIN_REFERENCE_TABLE = 'join_reference_table';
    public const JOIN_REFERENCE_FIELD = 'join_reference_field';
    public const JOIN_ON_FIELD = 'join_on_field';
    public const JOIN_FIELDS = 'fields';
    public const JOIN_FIELD = 'field';
    public const JOIN_FIELD_COLUMN = 'column';
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \Dom_Document) {
            return $output;
        }
        /** @var \DOMNodeList $types */
        $types = $source->get_elements_by_tag_name('extension_attributes');
        /** @var \DOMNode $type */
        foreach ($types as $type) {
            $type_config = [];
            $type_name = $type->get_attribute('for');
            $attributes = $type->get_elements_by_tag_name('attribute');
            foreach ($attributes as $attribute) {
                $code = $attribute->get_attribute('code');
                $code_type = $attribute->get_attribute('type');
                $resources_element = $attribute->get_elements_by_tag_name('resources')->item(0);
                $resource_refs = [];
                if ($resources_element && $resources_element->node_type === XML_ELEMENT_NODE) {
                    $single_resource_elements = $resources_element->get_elements_by_tag_name('resource');
                    foreach ($single_resource_elements as $element) {
                        if ($element->node_type != XML_ELEMENT_NODE) {
                            continue;
                        }
                        $resource_refs[] = $element->attributes->get_named_item('ref')->node_value;
                    }
                }
                $join_element = $attribute->get_elements_by_tag_name('join')->item(0);
                $join = $this->process_join_element($join_element, $attribute);
                $type_config[$code] = [self::DATA_TYPE => $code_type, self::RESOURCE_PERMISSIONS => $resource_refs, self::JOIN_DIRECTIVE => $join];
            }
            $output[$type_name] = $type_config;
        }
        return $output;
    }
    /**
     * Process the join element configuration
     *
     * @param \DOMElement $joinElement
     * @param \DOMElement $attribute
     * @return array
     */
    private function process_join_element($join_element, $attribute)
    {
        $join = null;
        if ($join_element && $join_element->node_type === XML_ELEMENT_NODE) {
            $join_attributes = $join_element->attributes;
            $join = [self::JOIN_REFERENCE_TABLE => $join_attributes->get_named_item('reference_table')->node_value, self::JOIN_ON_FIELD => $join_attributes->get_named_item('join_on_field')->node_value, self::JOIN_REFERENCE_FIELD => $join_attributes->get_named_item('reference_field')->node_value];
            $fields = $attribute->get_elements_by_tag_name('field');
            foreach ($fields as $field) {
                $column = $field->get_attribute('column');
                $join[self::JOIN_FIELDS][] = [self::JOIN_FIELD => $field->node_value, self::JOIN_FIELD_COLUMN => $column];
            }
        }
        return $join;
    }
}