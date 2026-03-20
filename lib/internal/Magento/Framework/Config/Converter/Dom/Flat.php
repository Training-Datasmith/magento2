<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config\Converter\Dom;

use Magento\Framework\Config\Dom\Array_Node_Config;
/**
 * Universal converter of any XML data to an array representation with no data loss
 *
 * @api
 * @since 100.0.2
 */
class Flat
{
    /**
     * @var ArrayNodeConfig
     */
    protected $array_node_config;
    /**
     * Constructor
     *
     * @param ArrayNodeConfig $arrayNodeConfig
     */
    public function __construct(Array_Node_Config $array_node_config)
    {
        $this->array_node_config = $array_node_config;
    }
    /**
     * Convert dom node tree to array in general case or to string in a case of a text node
     *
     * Example:
     * <node attr="val">
     *     <subnode>val2<subnode>
     * </node>
     *
     * is converted to
     *
     * array(
     *     'node' => array(
     *         'attr' => 'wal',
     *         'subnode' => 'val2'
     *     )
     * )
     *
     * @param \DOMNode $source
     * @param string $basePath
     * @return string|array
     * @throws \UnexpectedValueException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert(\Dom_Node $source, $base_path = '')
    {
        $value = [];
        /** @var \DOMNode $node */
        foreach ($source->child_nodes as $node) {
            if ($node->node_type == XML_ELEMENT_NODE) {
                $node_name = $node->node_name;
                $node_path = $base_path . '/' . $node_name;
                $array_key_attribute = $this->array_node_config->get_assoc_array_key_attribute($node_path);
                $is_numeric_array_node = $this->array_node_config->is_numeric_array($node_path);
                $is_array_node = $is_numeric_array_node || $array_key_attribute;
                if (isset($value[$node_name]) && !$is_array_node) {
                    throw new \UnexpectedValueException("Node path '{$node_path}' is not unique, but it has not been marked as array.");
                }
                $node_data = $this->convert($node, $node_path);
                if ($is_array_node) {
                    if ($is_numeric_array_node) {
                        $value[$node_name][] = $node_data;
                    } elseif (isset($node_data[$array_key_attribute])) {
                        $array_key_value = $node_data[$array_key_attribute];
                        $value[$node_name][$array_key_value] = $node_data;
                    } else {
                        throw new \UnexpectedValueException("Array is expected to contain value for key '{$array_key_attribute}'.");
                    }
                } else {
                    $value[$node_name] = $node_data;
                }
            } elseif ($node->node_type == XML_CDATA_SECTION_NODE || $node->node_type == XML_TEXT_NODE && trim($node->node_value) != '') {
                $value = $node->node_value;
                break;
            }
        }
        $result = $this->get_node_attributes($source);
        if (is_array($value)) {
            $result = array_merge($result, $value);
            if (!$result) {
                $result = '';
            }
        } else if ($result) {
            $result['value'] = trim($value);
        } else {
            $result = trim($value);
        }
        return $result;
    }
    /**
     * Retrieve key-value pairs of node attributes
     *
     * @param \DOMNode $node
     * @return array
     */
    protected function get_node_attributes(\Dom_Node $node)
    {
        $result = [];
        $attributes = $node->attributes ?: [];
        /** @var \DOMNode $attribute */
        foreach ($attributes as $attribute) {
            if ($attribute->node_type == XML_ATTRIBUTE_NODE) {
                $result[$attribute->node_name] = $attribute->node_value;
            }
        }
        return $result;
    }
}