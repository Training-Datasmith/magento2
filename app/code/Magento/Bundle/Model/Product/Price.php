<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Catalog\Api\Data\Product_Tier_Price_Extension_Factory;
use Magento\Catalog\Model\Pricing\Special_Price_Service;
use Magento\Customer\Api\Group_Management_Interface;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Pricing\Price_Currency_Interface;
/**
 * Bundle product type price model
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 100.0.2
 */
class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Fixed bundle price type
     */
    public const PRICE_TYPE_FIXED = 1;
    /**
     * Dynamic bundle price type
     */
    public const PRICE_TYPE_DYNAMIC = 0;
    /**
     * Flag which indicates - is min/max prices have been calculated by index
     *
     * @var bool
     */
    protected $_is_prices_calculated_by_index;
    /**
     * Catalog data variable
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalog_data = null;
    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;
    /**
     * Constructor
     *
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param ProductTierPriceExtensionFactory|null $tierPriceExtensionFactory
     * @param SpecialPriceService|null $specialPriceService
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Catalog_Rule\Model\Resource_Model\Rule_Factory $rule_factory, \Magento\Store\Model\Store_Manager_Interface $store_manager, \Magento\Framework\Stdlib\DateTime\Timezone_Interface $locale_date, \Magento\Customer\Model\Session $customer_session, \Magento\Framework\Event\Manager_Interface $event_manager, Price_Currency_Interface $price_currency, Group_Management_Interface $group_management, \Magento\Catalog\Api\Data\Product_Tier_Price_Interface_Factory $tier_price_factory, \Magento\Framework\App\Config\Scope_Config_Interface $config, \Magento\Catalog\Helper\Data $catalog_data, ?\Magento\Framework\Serialize\Serializer\Json $serializer = null, ?Product_Tier_Price_Extension_Factory $tier_price_extension_factory = null, ?Special_Price_Service $special_price_service = null)
    {
        $this->_catalog_data = $catalog_data;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct($rule_factory, $store_manager, $locale_date, $customer_session, $event_manager, $price_currency, $group_management, $tier_price_factory, $config, $tier_price_extension_factory, $special_price_service);
    }
    /**
     * Is min/max prices have been calculated by index
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_is_prices_calculated_by_index()
    {
        return $this->_is_prices_calculated_by_index;
    }
    /**
     * Return product base price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function get_price($product)
    {
        if ($product->get_price_type() == self::PRICE_TYPE_FIXED) {
            return $product->get_data('price');
        } else {
            return 0;
        }
    }
    /**
     * Get Total price  for Bundle items
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|float $qty
     * @return float
     */
    public function get_total_bundle_items_price($product, $qty = null)
    {
        $price = 0.0;
        if ($product->has_custom_options()) {
            $selection_ids = $this->get_bundle_selection_ids($product);
            if ($selection_ids) {
                $selections = $product->get_type_instance()->get_selections_by_ids($selection_ids, $product);
                $selections->add_tier_price_data();
                $this->_event_manager->dispatch('prepare_catalog_product_collection_prices', ['collection' => $selections, 'store_id' => $product->get_store_id()]);
                foreach ($selections->get_items() as $selection) {
                    if ($selection->is_salable()) {
                        $selection_qty = $product->get_custom_option('selection_qty_' . $selection->get_selection_id());
                        if ($selection_qty) {
                            $price += $this->get_selection_final_total_price($product, $selection, $qty, $selection_qty->get_value());
                        }
                    }
                }
            }
        }
        return $price;
    }
    /**
     * Retrieve array of bundle selection IDs
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function get_bundle_selection_ids(\Magento\Catalog\Model\Product $product)
    {
        $custom_option = $product->get_custom_option('bundle_selection_ids');
        if ($custom_option) {
            $selection_ids = $this->serializer->unserialize($custom_option->get_value());
            if (is_array($selection_ids) && !empty($selection_ids)) {
                return $selection_ids;
            }
        }
        return [];
    }
    /**
     * Get product final price
     *
     * @param float $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function get_final_price($qty, $product)
    {
        if ($qty === null && $product->get_calculated_final_price() !== null) {
            return $product->get_calculated_final_price();
        }
        $final_price = $this->get_base_price($product, $qty);
        $product->set_final_price($final_price);
        $this->_event_manager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $qty]);
        $final_price = $product->get_data('final_price');
        $final_price = $this->_apply_options_price($product, $qty, $final_price);
        $final_price += $this->get_total_bundle_items_price($product, $qty);
        $final_price = max(0, $final_price);
        $product->set_final_price($final_price);
        return $final_price;
    }
    /**
     * Returns final price of a child product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param float $productQty
     * @param \Magento\Catalog\Model\Product $childProduct
     * @param float $childProductQty
     * @return float
     */
    public function get_child_final_price($product, $product_qty, $child_product, $child_product_qty)
    {
        return $this->get_selection_final_total_price($product, $child_product, $product_qty, $child_product_qty, false);
    }
    /**
     * Retrieve Price considering tier price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string|null $which
     * @param bool|null $includeTax
     * @param bool $takeTierPrice
     * @return float|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function get_total_prices($product, $which = null, $include_tax = null, $take_tier_price = true)
    {
        // check calculated price index
        if ($product->get_data('min_price') && $product->get_data('max_price')) {
            $minimal_price = $this->_catalog_data->get_tax_price($product, $product->get_data('min_price'), $include_tax);
            $maximal_price = $this->_catalog_data->get_tax_price($product, $product->get_data('max_price'), $include_tax);
            $this->_is_prices_calculated_by_index = true;
        } else {
            /**
             * Check if product price is fixed
             */
            $final_price = $product->get_final_price();
            if ($product->get_price_type() == self::PRICE_TYPE_FIXED) {
                $minimal_price = $maximal_price = $this->_catalog_data->get_tax_price($product, $final_price, $include_tax);
            } else {
                // PRICE_TYPE_DYNAMIC
                $minimal_price = $maximal_price = 0;
            }
            $options = $this->get_options($product);
            $min_price_founded = false;
            if ($options) {
                foreach ($options as $option) {
                    /* @var $option \Magento\Bundle\Model\Option */
                    $selections = $option->get_selections();
                    if (empty($selections)) {
                        continue;
                    }
                    $selection_minimal_prices = [];
                    $selection_maximal_prices = [];
                    foreach ($option->get_selections() as $selection) {
                        /* @var $selection \Magento\Bundle\Model\Selection */
                        if (!$selection->is_salable()) {
                            /**
                             * @todo CatalogInventory Show out of stock Products
                             */
                            continue;
                        }
                        $qty = $selection->get_selection_qty();
                        $item = $product->get_price_type() == self::PRICE_TYPE_FIXED ? $product : $selection;
                        $selection_minimal_prices[] = $this->_catalog_data->get_tax_price($item, $this->get_selection_final_total_price($product, $selection, 1, $qty, true, $take_tier_price), $include_tax);
                        $selection_maximal_prices[] = $this->_catalog_data->get_tax_price($item, $this->get_selection_final_total_price($product, $selection, 1, null, true, $take_tier_price), $include_tax);
                    }
                    if (count($selection_minimal_prices)) {
                        $sel_min_price = min($selection_minimal_prices);
                        if ($option->get_required()) {
                            $minimal_price += $sel_min_price;
                            $min_price_founded = true;
                        } elseif (true !== $min_price_founded) {
                            $sel_min_price += $minimal_price;
                            $min_price_founded = false === $min_price_founded ? $sel_min_price : min($min_price_founded, $sel_min_price);
                        }
                        if ($option->is_multi_selection()) {
                            $maximal_price += array_sum($selection_maximal_prices);
                        } else {
                            $maximal_price += max($selection_maximal_prices);
                        }
                    }
                }
            }
            // condition is TRUE when all product options are NOT required
            if (!is_bool($min_price_founded)) {
                $minimal_price = $min_price_founded;
            }
            $custom_options = $product->get_options();
            if ($product->get_price_type() == self::PRICE_TYPE_FIXED && $custom_options) {
                foreach ($custom_options as $custom_option) {
                    /* @var $customOption \Magento\Catalog\Model\Product\Option */
                    $values = $custom_option->get_values();
                    if ($values) {
                        $prices = [];
                        foreach ($values as $value) {
                            /* @var $value \Magento\Catalog\Model\Product\Option\Value */
                            $value_price = $value->get_price(true);
                            $prices[] = $value_price;
                        }
                        if (count($prices) === 0) {
                            continue;
                        }
                        if ($custom_option->get_is_require()) {
                            $minimal_price += $this->_catalog_data->get_tax_price($product, min($prices), $include_tax);
                        }
                        $multi_types = [\Magento\Catalog\Api\Data\Product_Custom_Option_Interface::OPTION_TYPE_CHECKBOX, \Magento\Catalog\Api\Data\Product_Custom_Option_Interface::OPTION_TYPE_MULTIPLE];
                        if (in_array($custom_option->get_type(), $multi_types)) {
                            $maximal_value = array_sum($prices);
                        } else {
                            $maximal_value = max($prices);
                        }
                        $maximal_price += $this->_catalog_data->get_tax_price($product, $maximal_value, $include_tax);
                    } else {
                        $value_price = $custom_option->get_price(true);
                        if ($custom_option->get_is_require()) {
                            $minimal_price += $this->_catalog_data->get_tax_price($product, $value_price, $include_tax);
                        }
                        $maximal_price += $this->_catalog_data->get_tax_price($product, $value_price, $include_tax);
                    }
                }
            }
            $this->_is_prices_calculated_by_index = false;
        }
        if ($which == 'max') {
            return $maximal_price;
        } elseif ($which == 'min') {
            return $minimal_price;
        }
        return [$minimal_price, $maximal_price];
    }
    /**
     * Get Options with attached Selections collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function get_options($product)
    {
        $product->get_type_instance()->set_store_filter($product->get_store_id(), $product);
        $option_collection = $product->get_type_instance()->get_options_collection($product);
        $selection_collection = $product->get_type_instance()->get_selections_collection($product->get_type_instance()->get_options_ids($product), $product);
        return $option_collection->append_selections($selection_collection, false, false);
    }
    /**
     * Calculate price of selection
     *
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @param float|null $selectionQty
     * @param null|bool $multiplyQty Whether to multiply selection's price by its quantity
     * @return float
     *
     * @see \Magento\Bundle\Model\Product\Price::getSelectionFinalTotalPrice()
     */
    public function get_selection_price($bundle_product, $selection_product, $selection_qty = null, $multiply_qty = true)
    {
        return $this->get_selection_final_total_price($bundle_product, $selection_product, 0, $selection_qty, $multiply_qty);
    }
    /**
     * Calculate selection price for front view (with applied special of bundle)
     *
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @param float $qty
     * @return float
     */
    public function get_selection_pre_final_price($bundle_product, $selection_product, $qty = null)
    {
        return $this->get_selection_price($bundle_product, $selection_product, $qty);
    }
    /**
     * Calculate final price of selection with take into account tier price
     *
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @param float $bundleQty
     * @param float $selectionQty
     * @param bool $multiplyQty
     * @param bool $takeTierPrice
     * @return float
     */
    public function get_selection_final_total_price($bundle_product, $selection_product, $bundle_qty, $selection_qty, $multiply_qty = true, $take_tier_price = true)
    {
        if (null === $bundle_qty) {
            $bundle_qty = 1.0;
        }
        if ($selection_qty === null) {
            $selection_qty = $selection_product->get_selection_qty();
        }
        if ($bundle_product->get_price_type() == self::PRICE_TYPE_DYNAMIC) {
            $total_qty = $bundle_qty * $selection_qty;
            if (!$take_tier_price || $total_qty === 0) {
                $total_qty = 1;
            }
            $price = $selection_product->get_final_price($total_qty);
        } else if ($selection_product->get_selection_price_type()) {
            // percent
            $product = clone $bundle_product;
            $product->set_final_price($this->get_price($product));
            $this->_event_manager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $bundle_qty]);
            $price = $product->get_data('final_price') * ($selection_product->get_selection_price_value() / 100);
        } else {
            // fixed
            $price = $selection_product->get_selection_price_value();
        }
        if ($multiply_qty) {
            $price *= $selection_qty;
        }
        return min($price, $this->_apply_tier_price($bundle_product, $bundle_qty, $price), $this->_apply_special_price($bundle_product, $price));
    }
    /**
     * Apply tier price for bundle
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param float $qty
     * @param float $finalPrice
     * @return float
     */
    protected function _apply_tier_price($product, $qty, $final_price)
    {
        if ($qty === null) {
            return $final_price;
        }
        $tier_price = $product->get_tier_price($qty);
        if (is_numeric($tier_price)) {
            $tier_price = $final_price - $final_price * ($tier_price / 100);
            $final_price = min($final_price, $tier_price);
        }
        return $final_price;
    }
    /**
     * Get product tier price by qty
     *
     * @param float $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return float|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function get_tier_price($qty, $product)
    {
        $all_customers_group_id = $this->_group_management->get_all_customers_group()->get_id();
        $prices = $product->get_data('tier_price');
        if ($prices === null) {
            if ($attribute = $product->get_resource()->get_attribute('tier_price')) {
                $attribute->get_backend()->after_load($product);
                $prices = $product->get_data('tier_price');
            }
        }
        if ($prices === null || !is_array($prices)) {
            if ($qty !== null) {
                return $product->get_price();
            }
            return [['price' => $product->get_price(), 'website_price' => $product->get_price(), 'price_qty' => 1, 'cust_group' => $all_customers_group_id]];
        }
        $cust_group = $this->_get_customer_group_id($product);
        if ($qty) {
            $prev_qty = 1;
            $prev_price = 0;
            $prev_group = $all_customers_group_id;
            foreach ($prices as $price) {
                if (empty($price['percentage_value'])) {
                    // can use only percentage tier price
                    continue;
                }
                if ($price['cust_group'] != $cust_group && $price['cust_group'] != $all_customers_group_id) {
                    // tier not for current customer group nor is for all groups
                    continue;
                }
                if ($qty < $price['price_qty']) {
                    // tier is higher than product qty
                    continue;
                }
                if ($price['price_qty'] < $prev_qty) {
                    // higher tier qty already found
                    continue;
                }
                if ($price['price_qty'] == $prev_qty && $prev_group != $all_customers_group_id && $price['cust_group'] == $all_customers_group_id) {
                    // found tier qty is same as current tier qty but current tier group is ALL_GROUPS
                    continue;
                }
                if ($price['percentage_value'] > $prev_price) {
                    $prev_price = $price['percentage_value'];
                    $prev_qty = $price['price_qty'];
                    $prev_group = $price['cust_group'];
                }
            }
            return $prev_price;
        } else {
            $qty_cache = [];
            foreach ($prices as $i => $price) {
                if ($price['cust_group'] != $cust_group && $price['cust_group'] != $all_customers_group_id) {
                    unset($prices[$i]);
                } elseif (isset($qty_cache[$price['price_qty']])) {
                    $j = $qty_cache[$price['price_qty']];
                    if ($prices[$j]['website_price'] < $price['website_price']) {
                        unset($prices[$j]);
                        $qty_cache[$price['price_qty']] = $i;
                    } else {
                        unset($prices[$i]);
                    }
                } else {
                    $qty_cache[$price['price_qty']] = $i;
                }
            }
        }
        return $prices ? $prices : [];
    }
    /**
     * Calculate and apply special price
     *
     * @param float $finalPrice
     * @param float $specialPrice
     * @param string $specialPriceFrom
     * @param string $specialPriceTo
     * @param mixed $store
     * @return float
     */
    public function calculate_special_price($final_price, $special_price, $special_price_from, $special_price_to, $store = null)
    {
        if ($special_price !== null && $special_price != false) {
            $special_price_to = $this->get_special_price_service()->execute($special_price_to);
            if ($this->_locale_date->is_scope_date_in_interval($store, $special_price_from, $special_price_to)) {
                $special_price = $final_price * ($special_price / 100);
                $final_price = min($final_price, $special_price);
            }
        }
        return $final_price;
    }
    /**
     * Returns the lowest price after applying any applicable bundle discounts
     *
     * @param /Magento/Catalog/Model/Product $bundleProduct
     * @param float|string $price
     * @param int $bundleQty
     * @return float
     */
    public function get_lowest_price($bundle_product, $price, $bundle_qty = 1)
    {
        $price = (float) $price;
        return min($price, $this->_apply_tier_price($bundle_product, $bundle_qty, $price), $this->_apply_special_price($bundle_product, $price));
    }
}