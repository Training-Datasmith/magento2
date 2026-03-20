<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\View\Xsd\Media\Type_Data_Extractor_Pool;
/**
 * Class Converter convert xml to appropriate array
 */
class Converter implements \Magento\Framework\Config\Converter_Interface
{
    /**
     * @var \Magento\Framework\View\Xsd\Media\TypeDataExtractorPool
     */
    protected $extractor_pool;
    /**
     * @param TypeDataExtractorPool $extractorPool
     */
    public function __construct(Type_Data_Extractor_Pool $extractor_pool)
    {
        $this->extractor_pool = $extractor_pool;
    }
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $xpath = new \Domx_Path($source);
        $output = [];
        foreach ($xpath->evaluate('/view') as $type_node) {
            foreach ($type_node->child_nodes as $child_node) {
                if ($child_node->node_type != XML_ELEMENT_NODE) {
                    continue;
                }
                $result = $this->parse_nodes($child_node);
                $output = array_merge_recursive($output, $result);
            }
        }
        return $output;
    }
    /**
     * Parse node values from xml nodes
     *
     * @param \DOMElement $childNode
     * @return array
     */
    protected function parse_nodes($child_node)
    {
        $output = [];
        switch ($child_node->node_name) {
            case 'vars':
                $module_name = $child_node->get_attribute('module');
                $output[$child_node->tag_name][$module_name] = $this->parse_var_element($child_node);
                break;
            case 'exclude':
                /** @var $itemNode \DOMElement */
                foreach ($child_node->get_elements_by_tag_name('item') as $item_node) {
                    $item_type = $item_node->get_attribute('type');
                    $output[$child_node->tag_name][$item_type][] = $item_node->node_value;
                }
                break;
            case 'media':
                foreach ($child_node->child_nodes as $media_node) {
                    if ($media_node instanceof \Dom_Element) {
                        $media_nodes_array = $this->extractor_pool->node_processor($media_node->tag_name)->process($media_node, $child_node->tag_name);
                        $output = array_merge_recursive($output, $media_nodes_array);
                    }
                }
                break;
        }
        return $output;
    }
    /**
     * Recursive parser for <var> nodes
     *
     * @param \DOMElement $node
     * @return string|boolean|number|null|[]
     */
    protected function parse_var_element(\Dom_Element $node)
    {
        $result = [];
        for ($var_node = $node->first_child; $var_node !== null; $var_node = $var_node->next_sibling) {
            if ($var_node instanceof \Dom_Element && $var_node->tag_name == 'var') {
                $var_name = $var_node->get_attribute('name');
                $result[$var_name] = $this->parse_var_element($var_node);
            }
        }
        if (!count($result)) {
            $result = $node->node_value !== null && strtolower($node->node_value) !== 'true' && strtolower($node->node_value) !== 'false' ? $node->node_value : filter_var($node->node_value, FILTER_VALIDATE_BOOLEAN);
        }
        return $result;
    }
}