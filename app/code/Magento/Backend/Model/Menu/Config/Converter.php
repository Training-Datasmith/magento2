<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Config;

/**
 * Class Converter converts xml to appropriate array
 * @api
 * @since 100.0.2
 */
class Converter implements \Magento\Framework\Config\Converter_Interface
{
    /**
     * Converts xml to appropriate array
     *
     * @param mixed $dom
     * @return array
     */
    public function convert($dom)
    {
        $extracted_data = [];
        $attribute_names_list = ['id', 'title', 'toolTip', 'module', 'sortOrder', 'action', 'parent', 'resource', 'dependsOnModule', 'dependsOnConfig', 'target'];
        $xpath = new \Domx_Path($dom);
        $node_list = $xpath->query('/config/menu/*');
        for ($i = 0; $i < $node_list->length; $i++) {
            $item = [];
            $node = $node_list->item($i);
            $item['type'] = $node->node_name;
            foreach ($attribute_names_list as $name) {
                if ($node->has_attribute($name)) {
                    $item[$name] = $node->get_attribute($name);
                }
            }
            $extracted_data[] = $item;
        }
        return $extracted_data;
    }
}