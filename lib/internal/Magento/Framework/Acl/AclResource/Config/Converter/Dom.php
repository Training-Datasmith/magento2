<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl\Acl_Resource\Config\Converter;

/**
 * @inheritDoc
 */
class Dom implements \Magento\Framework\Config\Converter_Interface
{
    /**
     * @inheritdoc
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \Exception
     */
    public function convert($source)
    {
        $acl_resource_config = ['config' => ['acl' => ['resources' => []]]];
        $xpath = new \Domx_Path($source);
        /** @var $resourceNode \DOMNode */
        foreach ($xpath->query('/config/acl/resources/resource') as $resource_node) {
            $acl_resource_config['config']['acl']['resources'][] = $this->_convert_resource_node($resource_node);
        }
        return $acl_resource_config;
    }
    /**
     * Convert resource node into assoc array
     *
     * @param \DOMNode $resourceNode
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _convert_resource_node(\Dom_Node $resource_node)
    {
        $resource_data = [];
        $resource_attributes = $resource_node->attributes;
        $id_node = $resource_attributes->get_named_item('id');
        if ($id_node === null) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Attribute "id" is required for ACL resource.');
        }
        $resource_data['id'] = $id_node->node_value;
        $module_node = $resource_attributes->get_named_item('module');
        if ($module_node !== null) {
            $resource_data['module'] = $module_node->node_value;
        }
        $title_node = $resource_attributes->get_named_item('title');
        if ($title_node !== null) {
            $resource_data['title'] = $title_node->node_value;
        }
        $sort_order_node = $resource_attributes->get_named_item('sortOrder');
        $resource_data['sortOrder'] = $sort_order_node !== null ? (int) $sort_order_node->node_value : 0;
        $disabled_node = $resource_attributes->get_named_item('disabled');
        $resource_data['disabled'] = $disabled_node !== null && $disabled_node->node_value == 'true';
        // convert child resource nodes if needed
        $resource_data['children'] = [];
        /** @var $childNode \DOMNode */
        foreach ($resource_node->child_nodes as $child_node) {
            if ($child_node->node_name == 'resource') {
                $resource_data['children'][] = $this->_convert_resource_node($child_node);
            }
        }
        return $resource_data;
    }
}