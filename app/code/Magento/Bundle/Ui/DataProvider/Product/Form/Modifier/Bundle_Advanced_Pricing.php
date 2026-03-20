<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Ui\Data_Provider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\Product_Attribute_Interface;
use Magento\Catalog\Ui\Data_Provider\Product\Form\Modifier\Abstract_Modifier;
use Magento\Framework\Stdlib\Array_Manager;
/**
 * Customize Advanced Pricing modal panel
 */
class Bundle_Advanced_Pricing extends Abstract_Modifier
{
    public const CODE_PRICE_TYPE = 'price_type';
    public const CODE_MSRP = 'msrp';
    public const CODE_MSRP_DISPLAY_ACTUAL_PRICE_TYPE = 'msrp_display_actual_price_type';
    public const CODE_ADVANCED_PRICING = 'advanced-pricing';
    public const CODE_RECORD = 'record';
    /**
     * @var ArrayManager
     */
    private $array_manager;
    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(Array_Manager $array_manager)
    {
        $this->array_manager = $array_manager;
    }
    /**
     * @inheritdoc
     */
    public function modify_meta(array $meta)
    {
        $group_code = $this->get_group_code_by_field($meta, self::CODE_ADVANCED_PRICING);
        if ($group_code) {
            $parent_node =& $meta[$group_code]['children'][self::CODE_ADVANCED_PRICING]['children'];
            if (isset($parent_node['container_' . self::CODE_MSRP]) && isset($parent_node['container_' . self::CODE_MSRP_DISPLAY_ACTUAL_PRICE_TYPE])) {
                $parent_node = $this->modify_msrp_meta($parent_node);
            }
            if (isset($parent_node['container_' . Product_Attribute_Interface::CODE_SPECIAL_PRICE])) {
                $current_node =& $parent_node['container_' . Product_Attribute_Interface::CODE_SPECIAL_PRICE]['children'];
                $current_node[Product_Attribute_Interface::CODE_SPECIAL_PRICE]['arguments']['data']['config']['addbefore'] = '%';
            }
            $parent_node_children =& $parent_node[Product_Attribute_Interface::CODE_TIER_PRICE]['children'];
            if (isset($parent_node_children[self::CODE_RECORD]['children'][Product_Attribute_Interface::CODE_PRICE])) {
                $current_node =& $parent_node_children[self::CODE_RECORD]['children'][Product_Attribute_Interface::CODE_PRICE];
                $current_node['arguments']['data']['config']['label'] = __('Percent Discount');
            }
        }
        return $meta;
    }
    /**
     * @inheritdoc
     */
    public function modify_data(array $data)
    {
        return $data;
    }
    /**
     * Modify meta for MSRP fields.
     *
     * @param array $meta
     * @return array
     */
    private function modify_msrp_meta(array $meta)
    {
        $meta = $this->array_manager->merge($this->array_manager->find_path(static::CODE_MSRP, $meta, null, 'children') . static::META_CONFIG_PATH, $meta, ['imports' => ['disabled' => 'ns = ${ $.ns }, index = ' . static::CODE_PRICE_TYPE . ':checked', '__disableTmpl' => ['disabled' => false]]]);
        $meta = $this->array_manager->merge($this->array_manager->find_path(static::CODE_MSRP_DISPLAY_ACTUAL_PRICE_TYPE, $meta, null, 'children') . static::META_CONFIG_PATH, $meta, ['imports' => ['disabled' => 'ns = ${ $.ns }, index = ' . static::CODE_PRICE_TYPE . ':checked', '__disableTmpl' => ['disabled' => false]]]);
        return $meta;
    }
}