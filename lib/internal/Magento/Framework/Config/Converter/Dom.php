<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config\Converter;

class Dom implements \Magento\Framework\Config\Converter_Interface
{
    public const ATTRIBUTES = '__attributes__';
    public const CONTENT = '__content__';
    /**
     * Convert dom node tree to array
     *
     * @param mixed $source
     * @return array
     */
    public function convert($source)
    {
        $node_list_data = [];
        /** @var $node \DOMNode */
        foreach ($source->child_nodes as $node) {
            if ($node->node_type == XML_ELEMENT_NODE) {
                $node_data = [];
                /** @var $attribute \DOMNode */
                foreach ($node->attributes as $attribute) {
                    if ($attribute->node_type == XML_ATTRIBUTE_NODE) {
                        $node_data[self::ATTRIBUTES][$attribute->node_name] = $attribute->node_value;
                    }
                }
                $children_data = $this->convert($node);
                if (is_array($children_data)) {
                    $node_data = array_merge($node_data, $children_data);
                } else {
                    $node_data[self::CONTENT] = $children_data;
                }
                $node_list_data[$node->node_name][] = $node_data;
            } elseif ($node->node_type == XML_CDATA_SECTION_NODE || $node->node_type == XML_TEXT_NODE && trim($node->node_value) != '') {
                return $node->node_value;
            }
        }
        return $node_list_data;
    }
}