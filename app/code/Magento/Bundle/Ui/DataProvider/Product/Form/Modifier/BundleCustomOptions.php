<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Ui\Data_Provider\Product\Form\Modifier;

use Magento\Catalog\Ui\Data_Provider\Product\Form\Modifier\Abstract_Modifier;
use Magento\Catalog\Ui\Data_Provider\Product\Form\Modifier\Custom_Options;
use Magento\Ui\Component\Container;
/**
 * Customize "Customizable Options" panel
 */
class Bundle_Custom_Options extends Abstract_Modifier
{
    /**
     * @inheritdoc
     */
    public function modify_meta(array $meta)
    {
        if ($group_code = $this->get_group_code_by_field($meta, Custom_Options::CONTAINER_HEADER_NAME)) {
            $meta[$group_code]['children']['message'] = $this->get_error_message(0);
            if (!empty($meta[$group_code]['children'][Custom_Options::CONTAINER_HEADER_NAME])) {
                $meta = $this->modify_custom_options_button($meta, $group_code, Custom_Options::CONTAINER_HEADER_NAME, Custom_Options::BUTTON_IMPORT);
                $meta = $this->modify_custom_options_button($meta, $group_code, Custom_Options::CONTAINER_HEADER_NAME, Custom_Options::BUTTON_ADD);
            }
        }
        return $meta;
    }
    /**
     * Add visible configuration for the Custom Options buttons
     *
     * @param array $meta
     * @param string $group
     * @param string $container
     * @param string $button
     * @return array
     */
    public function modify_custom_options_button(array $meta, $group, $container, $button)
    {
        if (!empty($meta[$group]['children'][$container]['children'][$button])) {
            $meta[$group]['children'][$container]['children'][$button]['arguments']['data']['config']['imports'] = ['visible' => '!ns = ${ $.ns }, index = ' . Bundle_Price::CODE_PRICE_TYPE . ':checked', '__disableTmpl' => ['visible' => false]];
        }
        return $meta;
    }
    /**
     * Prepares configuration for the error message container
     *
     * @param int $sortOrder
     * @return array
     */
    public function get_error_message($sort_order)
    {
        return ['arguments' => ['data' => ['config' => ['component' => 'Magento_Ui/js/form/components/html', 'componentType' => Container::NAME, 'additionalClasses' => 'message message-error', 'content' => __('We can\'t save custom-defined options for bundles with dynamic pricing.'), 'sortOrder' => $sort_order, 'imports' => ['visible' => 'ns = ${ $.ns }, index = ' . Bundle_Price::CODE_PRICE_TYPE . ':checked', '__disableTmpl' => ['visible' => false]]]]]];
    }
    /**
     * @inheritdoc
     */
    public function modify_data(array $data)
    {
        return $data;
    }
}