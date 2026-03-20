<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Ui\Data_Provider\Product\Form\Modifier;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\Data\Product_Attribute_Interface;
use Magento\Catalog\Model\Locator\Locator_Interface;
use Magento\Catalog\Ui\Data_Provider\Product\Form\Modifier\Abstract_Modifier;
use Magento\Framework\Stdlib\Array_Manager;
/**
 * Customize Price field
 */
class Bundle_Price extends Abstract_Modifier
{
    public const CODE_PRICE_TYPE = 'price_type';
    public const CODE_TAX_CLASS_ID = 'tax_class_id';
    /**
     * @var ArrayManager
     */
    protected $array_manager;
    /**
     * @var LocatorInterface
     */
    protected $locator;
    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(Locator_Interface $locator, Array_Manager $array_manager)
    {
        $this->locator = $locator;
        $this->array_manager = $array_manager;
    }
    /**
     * @inheritdoc
     */
    public function modify_meta(array $meta)
    {
        $meta = $this->array_manager->merge($this->array_manager->find_path(static::CODE_PRICE_TYPE, $meta, null, 'children') . static::META_CONFIG_PATH, $meta, ['disabled' => (bool) $this->locator->get_product()->get_id(), 'valueMap' => ['false' => '1', 'true' => '0'], 'validation' => ['required-entry' => false]]);
        $meta = $this->array_manager->merge($this->array_manager->find_path(Product_Attribute_Interface::CODE_PRICE, $meta, self::DEFAULT_GENERAL_PANEL . '/children', 'children') . static::META_CONFIG_PATH, $meta, ['imports' => ['disabled' => 'ns = ${ $.ns }, index = ' . static::CODE_PRICE_TYPE . ':checked', '__disableTmpl' => ['disabled' => false]]]);
        $meta = $this->array_manager->merge($this->array_manager->find_path(static::CODE_TAX_CLASS_ID, $meta, null, 'children') . static::META_CONFIG_PATH, $meta, ['imports' => ['disabled' => 'ns = ${ $.ns }, index = ' . static::CODE_PRICE_TYPE . ':checked', '__disableTmpl' => ['disabled' => false]]]);
        if ($this->locator->get_product()->get_price_type() == Price::PRICE_TYPE_DYNAMIC) {
            $meta = $this->array_manager->merge($this->array_manager->find_path(static::CODE_TAX_CLASS_ID, $meta, null, 'children') . static::META_CONFIG_PATH, $meta, ['service' => ['template' => '']]);
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
}