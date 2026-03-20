<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\Config\Converter;

use Magento\Framework\Config\Converter_Interface;
/**
 * A converter of reports configuration.
 *
 * Converts configuration data stored in XML format into corresponding PHP array.
 */
class Xml implements Converter_Interface
{
    /**
     * Converts XML node into corresponding array.
     *
     * @return array|string
     */
    private function convert_node(\Dom_Node $source)
    {
        $result = [];
        if ($source->has_attributes()) {
            $attrs = $source->attributes;
            foreach ($attrs as $attr) {
                $result[$attr->name] = $attr->value;
            }
        }
        if ($source->has_child_nodes()) {
            $children = $source->child_nodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->node_type == XML_TEXT_NODE) {
                    $result['_value'] = $child->node_value;
                    return count($result) == 1 ? $result['_value'] : $result;
                }
            }
            foreach ($children as $child) {
                if ($child instanceof \Dom_Character_Data) {
                    continue;
                }
                $result[$child->node_name][] = $this->convert_node($child);
            }
        }
        return $result;
    }
    /**
     * Converts XML document into corresponding array.
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        return $this->convert_node($source);
    }
}