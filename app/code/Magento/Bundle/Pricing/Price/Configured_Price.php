<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Pricing\Adjustment\Bundle_Calculator_Interface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Item_Interface;
use Magento\Catalog\Pricing\Price as CatalogPrice;
use Magento\Catalog\Pricing\Price\Configured_Price_Interface;
use Magento\Catalog\Pricing\Price\Configured_Price_Selection;
use Magento\Framework\Pricing\Price_Currency_Interface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
/**
 * Configured price model
 * @api
 * @since 100.0.2
 */
class Configured_Price extends Catalog_Price\Final_Price implements Configured_Price_Interface
{
    /**
     * Price type configured
     */
    public const PRICE_CODE = self::CONFIGURED_PRICE_CODE;
    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;
    /**
     * @var null|ItemInterface
     */
    protected $item;
    /**
     * Serializer interface instance.
     *
     * @var JsonSerializer
     */
    private $serializer;
    /**
     * @var ConfiguredPriceSelection
     */
    private $configured_price_selection;
    /**
     * @var DiscountCalculator
     */
    private $discount_calculator;
    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param BundleCalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param ItemInterface|null $item
     * @param JsonSerializer|null $serializer
     * @param ConfiguredPriceSelection|null $configuredPriceSelection
     * @param DiscountCalculator|null $discountCalculator
     */
    public function __construct(Product $saleable_item, $quantity, Bundle_Calculator_Interface $calculator, Price_Currency_Interface $price_currency, ?Item_Interface $item = null, ?Json_Serializer $serializer = null, ?Configured_Price_Selection $configured_price_selection = null, ?Discount_Calculator $discount_calculator = null)
    {
        $this->item = $item;
        $this->serializer = $serializer ?: \Magento\Framework\App\Object_Manager::get_instance()->get(Json_Serializer::class);
        $this->configured_price_selection = $configured_price_selection ?: \Magento\Framework\App\Object_Manager::get_instance()->get(Configured_Price_Selection::class);
        $this->discount_calculator = $discount_calculator ?: \Magento\Framework\App\Object_Manager::get_instance()->get(Discount_Calculator::class);
        parent::__construct($saleable_item, $quantity, $calculator, $price_currency);
    }
    /**
     * Set item to the model
     *
     * @param ItemInterface $item
     * @return $this
     */
    public function set_item(Item_Interface $item)
    {
        $this->item = $item;
        return $this;
    }
    /**
     * Get Options with attached Selections collection.
     *
     * @return array|\Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function get_options()
    {
        $bundle_product = $this->product;
        $bundle_options = [];
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $type_instance = $bundle_product->get_type_instance();
        $bundle_options_ids = [];
        if ($this->item !== null) {
            // get bundle options
            $options_quote_item_option = $this->item->get_option_by_code('bundle_option_ids');
            if ($options_quote_item_option && $options_quote_item_option->get_value()) {
                $bundle_options_ids = $this->serializer->unserialize($options_quote_item_option->get_value());
            }
        }
        if ($bundle_options_ids) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
            $options_collection = $type_instance->get_options_by_ids($bundle_options_ids, $bundle_product);
            // get and add bundle selections collection
            $selections_quote_item_option = $this->item->get_option_by_code('bundle_selection_ids');
            $bundle_selection_ids = $this->serializer->unserialize($selections_quote_item_option->get_value());
            if ($bundle_selection_ids) {
                $selections_collection = $type_instance->get_selections_by_ids($bundle_selection_ids, $bundle_product);
                $bundle_options = $options_collection->append_selections($selections_collection, true);
            }
        }
        return $bundle_options;
    }
    /**
     * Option amount calculation for bundle product.
     *
     * @param float $baseValue
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_configured_amount($base_value = 0.0)
    {
        $selection_price_list = $this->configured_price_selection->get_selection_price_list($this);
        return $this->calculator->calculate_bundle_amount($base_value, $this->product, $selection_price_list);
    }
    /**
     * Get price value
     *
     * @return float
     */
    public function get_value()
    {
        if ($this->item && $this->item->get_product()->get_id()) {
            $configured_options_amount = $this->get_configured_amount()->get_base_amount();
            return parent::get_value() + $this->discount_calculator->calculate_discount($this->item->get_product(), $configured_options_amount);
        }
        return parent::get_value();
    }
    /**
     * Get Amount for configured price which is included amount for all selected options
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_amount()
    {
        return $this->item ? $this->get_configured_amount($this->get_base_price()->get_value()) : parent::get_amount();
    }
}