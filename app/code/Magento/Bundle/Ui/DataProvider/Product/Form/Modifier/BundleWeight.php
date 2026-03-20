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
 * Customize Weight field
 */
class Bundle_Weight extends Abstract_Modifier
{
    public const CODE_WEIGHT_TYPE = 'weight_type';
    /**
     * @var ArrayManager
     */
    protected $array_manager;
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
        $meta = $this->array_manager->merge($this->array_manager->find_path(static::CODE_WEIGHT_TYPE, $meta, null, 'children') . static::META_CONFIG_PATH, $meta, ['valueMap' => ['false' => '1', 'true' => '0'], 'validation' => ['required-entry' => false]]);
        $meta = $this->array_manager->merge($this->array_manager->find_path(Product_Attribute_Interface::CODE_HAS_WEIGHT, $meta, null, 'children') . static::META_CONFIG_PATH, $meta, ['disabled' => true, 'visible' => false]);
        $meta = $this->array_manager->merge($this->array_manager->find_path(Product_Attribute_Interface::CODE_WEIGHT, $meta, null, 'children') . static::META_CONFIG_PATH, $meta, ['imports' => ['disabled' => 'ns = ${ $.ns }, index = ' . static::CODE_WEIGHT_TYPE . ':checked', '__disableTmpl' => ['disabled' => false]]]);
        return $meta;
    }
    /**
     * @inheritdoc
     */
    public function modify_data(array $data)
    {
        return $data;
    }
}