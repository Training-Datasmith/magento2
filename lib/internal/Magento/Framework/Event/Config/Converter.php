<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event\Config;

/**
 * Converter of event observers configuration from \DOMDocument to tree array.
 */
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
        /** @var \DOMNodeList $events */
        $events = $source->get_elements_by_tag_name('event');
        /** @var \DOMNode $eventConfig */
        foreach ($events as $event_config) {
            $event_name = $event_config->attributes->get_named_item('name')->node_value;
            $event_observers = [];
            /** @var \DOMNode $observerConfig */
            foreach ($event_config->child_nodes as $observer_config) {
                if ($observer_config->node_name != 'observer' || $observer_config->node_type != XML_ELEMENT_NODE) {
                    continue;
                }
                $observer_name_node = $observer_config->attributes->get_named_item('name');
                if (!$observer_name_node) {
                    throw new \InvalidArgumentException('Attribute name is missed');
                }
                $config = $this->_convert_observer_config($observer_config);
                $config['name'] = $observer_name_node->node_value;
                $event_observers[$observer_name_node->node_value] = $config;
            }
            $output[mb_strtolower($event_name ?? '')] = $event_observers;
        }
        return $output;
    }
    /**
     * Convert observer configuration
     *
     * @param \DOMNode $observerConfig
     * @return array
     */
    public function _convert_observer_config($observer_config)
    {
        $output = [];
        /** Parse instance configuration */
        $instance_attribute = $observer_config->attributes->get_named_item('instance');
        if ($instance_attribute) {
            $output['instance'] = $instance_attribute->node_value;
        }
        /** Parse instance method configuration */
        $method_attribute = $observer_config->attributes->get_named_item('method');
        if ($method_attribute) {
            $output['method'] = $method_attribute->node_value;
        }
        /** Parse disabled/enabled configuration */
        $disabled_attribute = $observer_config->attributes->get_named_item('disabled');
        if ($disabled_attribute && $disabled_attribute->node_value == 'true') {
            $output['disabled'] = true;
        }
        /** Parse shareability configuration */
        $shred_attribute = $observer_config->attributes->get_named_item('shared');
        if ($shred_attribute && $shred_attribute->node_value == 'false') {
            $output['shared'] = false;
        }
        return $output;
    }
}