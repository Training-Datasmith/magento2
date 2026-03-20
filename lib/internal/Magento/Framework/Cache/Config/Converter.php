<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache\Config;

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
        $output = [];
        /** @var \DOMNodeList $types */
        $types = $source->get_elements_by_tag_name('type');
        /** @var \DOMNode $type */
        foreach ($types as $type) {
            $type_config = [];
            foreach ($type->attributes as $attribute) {
                $type_config[$attribute->node_name] = $attribute->node_value;
            }
            /** @var \DOMNode $childNode */
            foreach ($type->child_nodes as $child_node) {
                if ($child_node->node_type == XML_ELEMENT_NODE || ($child_node->node_type == XML_CDATA_SECTION_NODE || $child_node->node_type == XML_TEXT_NODE && trim($child_node->node_value) != '')) {
                    $type_config[$child_node->node_name] = $child_node->node_value;
                }
            }
            $output[$type->attributes->get_named_item('name')->node_value] = $type_config;
        }
        return ['types' => $output];
    }
}