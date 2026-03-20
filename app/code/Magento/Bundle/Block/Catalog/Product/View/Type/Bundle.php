<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Catalog\Product\View\Type;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Price_Factory;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\Abstract_View;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\Final_Price;
use Magento\Catalog\Pricing\Price\Regular_Price;
use Magento\Catalog_Rule\Model\Resource_Model\Product\Collection_Processor;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Data_Object;
use Magento\Framework\Json\Encoder_Interface;
use Magento\Framework\Locale\Format_Interface;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\Pricing\Price_Currency_Interface;
use Magento\Framework\Stdlib\Array_Utils;
/**
 * Catalog bundle product info block
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bundle extends Abstract_View implements Reset_After_Request_Interface
{
    /**
     * @var array
     */
    protected $options;
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalog_product;
    /**
     * @var PriceFactory
     */
    protected $product_price_factory;
    /**
     * @var EncoderInterface
     */
    protected $json_encoder;
    /**
     * @var FormatInterface
     */
    protected $locale_format;
    /**
     * @var array
     */
    private $selected_options = [];
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor
     */
    private $catalog_rule_processor;
    /**
     * @var array
     */
    private $options_position = [];
    /**
     * @var PriceCurrencyInterface
     */
    private $price_currency;
    /**
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param PriceFactory $productPrice
     * @param EncoderInterface $jsonEncoder
     * @param FormatInterface $localeFormat
     * @param array $data
     * @param CollectionProcessor|null $catalogRuleProcessor
     * @param PriceCurrencyInterface|null $priceCurrency
     */
    public function __construct(Context $context, Array_Utils $array_utils, \Magento\Catalog\Helper\Product $catalog_product, Price_Factory $product_price, Encoder_Interface $json_encoder, Format_Interface $locale_format, array $data = [], ?Collection_Processor $catalog_rule_processor = null, ?Price_Currency_Interface $price_currency = null)
    {
        $this->catalog_product = $catalog_product;
        $this->product_price_factory = $product_price;
        $this->json_encoder = $json_encoder;
        $this->locale_format = $locale_format;
        parent::__construct($context, $array_utils, $data);
        $this->catalog_rule_processor = $catalog_rule_processor ?? Object_Manager::get_instance()->get(Collection_Processor::class);
        $this->price_currency = $price_currency ?? Object_Manager::get_instance()->get(Price_Currency_Interface::class);
    }
    /**
     * Returns the bundle product options
     *
     * Will return cached options data if the product options are already initialized
     * In a case when $stripSelection parameter is true will reload stored bundle selections collection from DB
     *
     * @param bool $stripSelection
     * @return array
     */
    public function get_options($strip_selection = false)
    {
        if (!$this->options) {
            $product = $this->get_product();
            /** @var Type $typeInstance */
            $type_instance = $product->get_type_instance();
            $type_instance->set_store_filter($product->get_store_id(), $product);
            $option_collection = $type_instance->get_options_collection($product);
            $selection_collection = $type_instance->get_selections_collection($type_instance->get_options_ids($product), $product);
            $this->catalog_rule_processor->add_price_data($selection_collection);
            $selection_collection->add_tier_price_data();
            $this->options = $option_collection->append_selections($selection_collection, $strip_selection, $this->catalog_product->get_skip_saleable_check());
        }
        return $this->options;
    }
    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function has_options()
    {
        $this->get_options();
        return !(empty($this->options) || !$this->get_product()->is_salable());
    }
    /**
     * Returns JSON encoded config to be used in JS scripts
     *
     * @return string
     */
    public function get_json_config()
    {
        /** @var Option[] $optionsArray */
        $options_array = $this->get_options();
        $options = [];
        $current_product = $this->get_product();
        $default_values = [];
        $pre_configured_flag = $current_product->has_preconfigured_values();
        /** @var DataObject|null $preConfiguredValues */
        $pre_configured_values = $pre_configured_flag ? $current_product->get_preconfigured_values() : null;
        $position = 0;
        foreach ($options_array as $option_item) {
            /* @var $optionItem Option */
            if (!$option_item->get_selections()) {
                continue;
            }
            $option_id = $option_item->get_id();
            $options[$option_id] = $this->get_option_item_data($option_item, $current_product, $position);
            $this->options_position[$position] = $option_id;
            // Add attribute default value (if set)
            if ($pre_configured_flag) {
                $config_value = $pre_configured_values->get_data('bundle_option/' . $option_id);
                if ($config_value) {
                    $default_values[$option_id] = $config_value;
                }
                $options = $this->process_options($option_id, $options, $pre_configured_values);
            }
            $position++;
        }
        $config = $this->get_config_data($current_product, $options);
        $config_obj = new Data_Object(['config' => $config]);
        //pass the return array encapsulated in an object for the other modules to be able to alter it eg: weee
        $this->_event_manager->dispatch('catalog_product_option_price_configuration_after', ['configObj' => $config_obj]);
        $config = $config_obj->get_config();
        if ($pre_configured_flag && !empty($default_values)) {
            $config['defaultValues'] = $default_values;
        }
        return $this->json_encoder->encode($config);
    }
    /**
     * Get html for option
     *
     * @param Option $option
     * @return string
     */
    public function get_option_html(Option $option)
    {
        $option_block = $this->get_child_block($option->get_type());
        if (!$option_block) {
            return __('There is no defined renderer for "%1" option type.', $this->escape_html($option->get_type()));
        }
        return $option_block->set_option($option)->to_html();
    }
    /**
     * Get formed data from option selection item.
     *
     * @param Product $product
     * @param Product $selection
     *
     * @return array
     */
    private function get_selection_item_data(Product $product, Product $selection)
    {
        $qty = $selection->get_selection_qty() * 1 ?: '1';
        $option_price_amount = $product->get_price_info()->get_price(\Magento\Bundle\Pricing\Price\Bundle_Option_Price::PRICE_CODE)->get_option_selection_amount($selection);
        $final_price = $option_price_amount->get_value();
        $base_price = $option_price_amount->get_base_amount();
        $old_price = $product->get_price_info()->get_price(\Magento\Bundle\Pricing\Price\Bundle_Option_Regular_Price::PRICE_CODE)->get_option_selection_amount($selection)->get_value();
        return ['qty' => $qty, 'customQty' => $selection->get_selection_can_change_qty(), 'optionId' => $selection->get_id(), 'prices' => ['oldPrice' => ['amount' => $this->price_currency->round_price($old_price)], 'basePrice' => ['amount' => $this->price_currency->round_price($base_price)], 'finalPrice' => ['amount' => $this->price_currency->round_price($final_price)]], 'priceType' => $selection->get_selection_price_type(), 'tierPrice' => $this->get_tier_prices($product, $selection), 'name' => $selection->get_name(), 'canApplyMsrp' => false];
    }
    /**
     * Get tier prices from option selection item
     *
     * @param Product $product
     * @param Product $selection
     * @return array
     */
    private function get_tier_prices(Product $product, Product $selection)
    {
        // recalculate currency
        $tier_prices = $selection->get_price_info()->get_price(\Magento\Catalog\Pricing\Price\Tier_Price::PRICE_CODE)->get_tier_price_list();
        foreach ($tier_prices as &$tier_price_info) {
            /** @var \Magento\Framework\Pricing\Amount\Base $price */
            $price = $tier_price_info['price'];
            $price_base_amount = $price->get_base_amount();
            $price_value = $price->get_value();
            $bundle_product_price = $this->product_price_factory->create();
            $price_base_amount = $bundle_product_price->get_lowest_price($product, $price_base_amount);
            $price_value = $bundle_product_price->get_lowest_price($product, $price_value);
            $tier_price_info['prices'] = ['oldPrice' => ['amount' => $price_base_amount], 'basePrice' => ['amount' => $price_base_amount], 'finalPrice' => ['amount' => $price_value]];
        }
        return $tier_prices;
    }
    /**
     * Get formed data from selections of option
     *
     * @param Option $option
     * @param Product $product
     * @return array
     */
    private function get_selections(Option $option, Product $product)
    {
        $selections = [];
        $selection_count = count($option->get_selections());
        foreach ($option->get_selections() as $selection_item) {
            /* @var $selectionItem Product */
            $selection_id = $selection_item->get_selection_id();
            $selections[$selection_id] = $this->get_selection_item_data($product, $selection_item);
            if (($selection_item->get_is_default() || $selection_count == 1 && $option->get_required()) && $selection_item->is_salable()) {
                $this->selected_options[$option->get_id()][] = $selection_id;
            }
        }
        return $selections;
    }
    /**
     * Get formed data from option
     *
     * @param Option $option
     * @param Product $product
     * @param int $position
     * @return array
     */
    private function get_option_item_data(Option $option, Product $product, $position)
    {
        return ['selections' => $this->get_selections($option, $product), 'title' => $option->get_title(), 'isMulti' => in_array($option->get_type(), ['multi', 'checkbox']), 'position' => $position];
    }
    /**
     * Get formed config data from calculated options data
     *
     * @param Product $product
     * @param array $options
     * @return array
     */
    private function get_config_data(Product $product, array $options)
    {
        $is_fixed_price = $this->get_product()->get_price_type() == Price::PRICE_TYPE_FIXED;
        $product_amount = $product->get_price_info()->get_price(Final_Price::PRICE_CODE)->get_price_without_option();
        $base_product_amount = $product->get_price_info()->get_price(Regular_Price::PRICE_CODE)->get_amount();
        $config = ['options' => $options, 'selected' => $this->selected_options, 'positions' => $this->options_position, 'bundleId' => $product->get_id(), 'priceFormat' => $this->locale_format->get_price_format(), 'prices' => ['oldPrice' => ['amount' => $is_fixed_price ? $base_product_amount->get_value() : 0], 'basePrice' => ['amount' => $is_fixed_price ? $product_amount->get_base_amount() : 0], 'finalPrice' => ['amount' => $is_fixed_price ? $product_amount->get_value() : 0]], 'priceType' => $product->get_price_type(), 'isFixedPrice' => $is_fixed_price];
        return $config;
    }
    /**
     * Set preconfigured quantities and selections to options.
     *
     * @param string $optionId
     * @param array $options
     * @param DataObject $preConfiguredValues
     * @return array
     */
    private function process_options(string $option_id, array $options, Data_Object $pre_configured_values)
    {
        $pre_configured_qtys = $pre_configured_values->get_data("bundle_option_qty/{$option_id}") ?? [];
        $selections = $options[$option_id]['selections'];
        array_walk($selections, function (&$selection, $selection_id) use ($pre_configured_qtys) {
            if (is_array($pre_configured_qtys) && isset($pre_configured_qtys[$selection_id])) {
                $selection['qty'] = $pre_configured_qtys[$selection_id];
            } else if ((int) $pre_configured_qtys > 0) {
                $selection['qty'] = $pre_configured_qtys;
            }
        });
        $options[$option_id]['selections'] = $selections;
        return $options;
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->selected_options = [];
        $this->options_position = [];
    }
}