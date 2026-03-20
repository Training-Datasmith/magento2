<?php

declare (strict_types=1);
/**
 * Initial configuration data converter. Converts \DOMDocument to array
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Initial;

/**
 * Class Converter
 */
class Converter implements \Magento\Framework\Config\Converter_Interface
{
    /**
     * Node paths to process
     *
     * @var array
     */
    protected $_node_map = [];
    /**
     * @var array
     */
    protected $_metadata = [];
    /**
     * @param array $nodeMap
     */
    public function __construct(array $node_map = [])
    {
        $this->_node_map = $node_map;
    }
    /**
     * Convert config data
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        $xpath = new \Domx_Path($source);
        $this->_metadata = [];
        /** @var $node \DOMNode */
        foreach ($xpath->query(implode(' | ', $this->_node_map)) as $node) {
            $output = array_merge($output, $this->_convert_node($node));
        }
        return ['data' => $output, 'metadata' => $this->_metadata];
    }
    /**
     * Convert node oto array
     *
     * @param \DOMNode $node
     * @param string $path
     * @return array|string|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convert_node(\Dom_Node $node, $path = '')
    {
        $output = [];
        if ($node->node_type == XML_ELEMENT_NODE) {
            if ($node->has_attributes()) {
                $backend_model = $node->attributes->get_named_item('backend_model');
                if ($backend_model) {
                    $this->_metadata[$path] = ['backendModel' => $backend_model->node_value];
                }
            }
            $node_data = [];
            /** @var $childNode \DOMNode */
            foreach ($node->child_nodes as $child_node) {
                $children_data = $this->_convert_node($child_node, ($path ? $path . '/' : '') . $child_node->node_name);
                if ($children_data == null) {
                    continue;
                }
                if (is_array($children_data)) {
                    $node_data = array_merge($node_data, $children_data);
                } else {
                    $node_data = $children_data;
                }
            }
            if (is_array($node_data) && empty($node_data)) {
                $node_data = null;
            }
            $output[$node->node_name] = $node_data;
        } elseif ($node->node_type == XML_CDATA_SECTION_NODE || $node->node_type == XML_TEXT_NODE && trim($node->node_value) != '') {
            return $node->node_value;
        }
        return $output;
    }
}