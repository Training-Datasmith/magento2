<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver;

use Magento\Catalog_Graph_Ql\Model\Price_Range_Data_Provider;
use Magento\Catalog_Graph_Ql\Model\Resolver\Product\Price\Discount;
use Magento\Catalog_Graph_Ql\Model\Resolver\Product\Price\Provider_Pool as PriceProviderPool;
use Magento\Catalog_Graph_Ql\Model\Resolver\Products\Data_Provider\Deferred\Product as ProductDataProvider;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
/**
 * Format product's pricing information for price_range field
 */
class Price_Range implements Resolver_Interface
{
    /**
     * @var Discount
     */
    private Discount $discount;
    /**
     * @var PriceProviderPool
     */
    private Price_Provider_Pool $price_provider_pool;
    /**
     * @var ProductDataProvider
     */
    private Product_Data_Provider $product_data_provider;
    /**
     * @var PriceRangeDataProvider
     */
    private Price_Range_Data_Provider $price_range_data_provider;
    /**
     * @param PriceProviderPool $priceProviderPool
     * @param Discount $discount
     * @param ProductDataProvider|null $productDataProvider
     * @param PriceRangeDataProvider|null $priceRangeDataProvider
     */
    public function __construct(Price_Provider_Pool $price_provider_pool, Discount $discount, ?Product_Data_Provider $product_data_provider = null, ?Price_Range_Data_Provider $price_range_data_provider = null)
    {
        $this->price_provider_pool = $price_provider_pool;
        $this->discount = $discount;
        $this->product_data_provider = $product_data_provider ?? Object_Manager::get_instance()->get(Product_Data_Provider::class);
        $this->price_range_data_provider = $price_range_data_provider ?? Object_Manager::get_instance()->get(Price_Range_Data_Provider::class);
    }
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        $this->product_data_provider->add_product_sku($value['sku']);
        $product_data = $this->product_data_provider->get_product_by_sku($value['sku'], $context);
        $value['model'] = $product_data['model'];
        return $this->price_range_data_provider->prepare($context, $info, $value);
    }
}