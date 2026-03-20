<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Ui\Data_Provider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Catalog\Api\Data\Product_Render\Price_Info_Interface;
use Magento\Catalog\Api\Data\Product_Render\Price_Info_Interface_Factory;
use Magento\Catalog\Api\Data\Product_Render_Interface;
use Magento\Catalog\Model\Product_Render\Formatted_Price_Info_Builder;
use Magento\Catalog\Ui\Data_Provider\Product\Product_Render_Collector_Interface;
use Magento\Framework\Pricing\Price_Currency_Interface;
/**
 * Collect information about bundle price
 *
 * This information can be used on front in order to render product list or product view
 * Price is collected always with VAT and fixed taxes
 */
class Bundle_Price implements Product_Render_Collector_Interface
{
    /**
     * Product type code
     */
    public const PRODUCT_TYPE = 'bundle';
    /**
     * @var PriceCurrencyInterface
     */
    private $price_currency;
    /**
     * @var array
     */
    private $exclude_adjustments;
    /**
     * @var PriceInfoInterfaceFactory
     */
    private $price_info_factory;
    /**
     * @var FormattedPriceInfoBuilder
     */
    private $formatted_price_info_builder;
    /**
     * BundlePrice constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param PriceInfoInterfaceFactory $priceInfoFactory
     * @param FormattedPriceInfoBuilder $formattedPriceInfoBuilder
     * @param array $excludeAdjustments
     */
    public function __construct(Price_Currency_Interface $price_currency, Price_Info_Interface_Factory $price_info_factory, Formatted_Price_Info_Builder $formatted_price_info_builder, array $exclude_adjustments = [])
    {
        $this->price_currency = $price_currency;
        $this->exclude_adjustments = $exclude_adjustments;
        $this->price_info_factory = $price_info_factory;
        $this->formatted_price_info_builder = $formatted_price_info_builder;
    }
    /**
     * @inheritdoc
     */
    public function collect(Product_Interface $product, Product_Render_Interface $product_render)
    {
        if ($product->get_type_id() == self::PRODUCT_TYPE) {
            $price_info = $product_render->get_price_info();
            if (!$product_render->get_price_info()) {
                /** @var PriceInfoInterface $priceInfo */
                $price_info = $this->price_info_factory->create();
            }
            $price_info->set_max_price($product->get_price_info()->get_price('final_price')->get_maximal_price()->get_value());
            $price_info->set_max_regular_price($product->get_price_info()->get_price('regular_price')->get_maximal_price()->get_value());
            $price_info->set_minimal_price($product->get_price_info()->get_price('final_price')->get_minimal_price()->get_value());
            $price_info->set_minimal_regular_price($product->get_price_info()->get_price('regular_price')->get_minimal_price()->get_value());
            $this->formatted_price_info_builder->build($price_info, $product_render->get_store_id(), $product_render->get_currency_code());
            $product_render->set_price_info($price_info);
        }
    }
}