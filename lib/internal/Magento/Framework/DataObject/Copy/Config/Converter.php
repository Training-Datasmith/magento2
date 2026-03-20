<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data_Object\Copy\Config;

class Converter implements \Magento\Framework\Config\Converter_Interface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $fieldsets = [];
        $xpath = new \Domx_Path($source);
        /** @var \DOMNode $fieldset */
        foreach ($xpath->query('/config/scope') as $scope) {
            $scope_id = $scope->attributes->get_named_item('id')->node_value;
            $fieldsets[$scope_id] = $this->_convert_scope($scope);
        }
        return $fieldsets;
    }
    /**
     * Convert Scope node to Magento array
     *
     * @param \DOMNode $scope
     * @return array
     */
    protected function _convert_scope($scope)
    {
        $result = [];
        foreach ($scope->child_nodes as $fieldset) {
            if (!$fieldset instanceof \Dom_Element) {
                continue;
            }
            $fieldset_name = $fieldset->attributes->get_named_item('id')->node_value;
            $result[$fieldset_name] = $this->_convert_fieldset($fieldset);
        }
        return $result;
    }
    /**
     * Convert Fieldset node to Magento array
     *
     * @param \DOMNode $fieldset
     * @return array
     */
    protected function _convert_fieldset($fieldset)
    {
        $result = [];
        foreach ($fieldset->child_nodes as $field) {
            if (!$field instanceof \Dom_Element) {
                continue;
            }
            $field_name = $field->attributes->get_named_item('name')->node_value;
            $result[$field_name] = $this->_convert_field($field);
        }
        return $result;
    }
    /**
     * Convert Field node to Magento array
     *
     * @param \DOMNode $field
     * @return array
     */
    protected function _convert_field($field)
    {
        $result = [];
        foreach ($field->child_nodes as $aspect) {
            if (!$aspect instanceof \Dom_Element) {
                continue;
            }
            /** @var \DOMNamedNodeMap $aspectAttributes */
            $aspect_attributes = $aspect->attributes;
            $aspect_name = $aspect_attributes->get_named_item('name')->node_value;
            $target_field = $aspect_attributes->get_named_item('targetField');
            $result[$aspect_name] = $target_field === null ? '*' : $target_field->node_value;
        }
        return $result;
    }
}