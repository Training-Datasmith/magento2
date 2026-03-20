<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver\Options;

use Magento\Catalog_Graph_Ql\Model\Resolver\Products\Data_Provider\Deferred\Product as ProductDataProvider;
use Magento\Catalog_Graph_Ql\Model\Resolver\Products\Data_Provider\Deferred\Product_Factory as ProductDataProviderFactory;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Query\Resolver\Value_Factory;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
/**
 * Bundle product option label resolver
 */
class Label implements Resolver_Interface
{
    /**
     * @var ValueFactory
     */
    private Value_Factory $value_factory;
    /**
     * @var ProductDataProviderFactory
     */
    private Product_Data_Provider_Factory $product_factory;
    /**
     * @param ValueFactory $valueFactory
     * @param ProductDataProvider $product Deprecated.  Use $productFactory
     * @param ProductDataProviderFactory|null $productFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Value_Factory $value_factory, Product_Data_Provider $product, ?Product_Data_Provider_Factory $product_factory = null)
    {
        $this->value_factory = $value_factory;
        $this->product_factory = $product_factory ?: Object_Manager::get_instance()->get(Product_Data_Provider_Factory::class);
    }
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['sku'])) {
            throw new Localized_Exception(__('"sku" value should be specified'));
        }
        $product = $this->product_factory->create();
        $product->add_product_sku($value['sku']);
        $product->add_eav_attributes(['name']);
        $result = function () use ($value, $context, $product) {
            $product_data = $product->get_product_by_sku($value['sku'], $context);
            /** @var \Magento\Catalog\Model\Product $productModel */
            $product_model = isset($product_data['model']) ? $product_data['model'] : null;
            return $product_model ? $product_model->get_name() : null;
        };
        return $this->value_factory->create($result);
    }
}