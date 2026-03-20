<?php

declare (strict_types=1);
/**
 * Converter of resources configuration from \DOMDocument to array
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Resource_Connection\Config;

class Converter implements \Magento\Framework\Config\Converter_Interface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $resources */
        $resources = $source->get_elements_by_tag_name('resource');
        /** @var \DOMNode $resourceConfig */
        foreach ($resources as $resource_config) {
            $resource_name = $resource_config->attributes->get_named_item('name')->node_value;
            $resource_data = [];
            foreach ($resource_config->attributes as $attribute) {
                $resource_data[$attribute->node_name] = $attribute->node_value;
            }
            $output[$resource_name] = $resource_data;
        }
        return $output;
    }
}