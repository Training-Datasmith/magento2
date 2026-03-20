<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Helper\Catalog\Product;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Pricing\Price\Tax_Price;
use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Catalog\Helper\Product\Configuration as ProductConfiguration;
use Magento\Catalog\Helper\Product\Configuration\Configuration_Interface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Item_Interface;
use Magento\Framework\App\Helper\Abstract_Helper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Helper for fetching properties by product configuration item
 * @api
 * @since 100.0.2
 */
class Configuration extends Abstract_Helper implements Configuration_Interface
{
    /**
     * Core data
     *
     * @var Data
     */
    protected $pricing_helper;
    /**
     * Catalog product configuration
     *
     * @var ProductConfiguration
     */
    protected $product_configuration;
    /**
     * @var Escaper
     */
    protected $escaper;
    /**
     * Serializer interface instance.
     *
     * @var Json
     */
    private $serializer;
    /**
     * @var TaxPrice
     */
    private $tax_helper;
    /**
     * @param Context $context
     * @param ProductConfiguration $productConfiguration
     * @param Data $pricingHelper
     * @param Escaper $escaper
     * @param Json|null $serializer
     * @param TaxPrice|null $taxHelper
     */
    public function __construct(Context $context, Product_Configuration $product_configuration, Data $pricing_helper, Escaper $escaper, ?Json $serializer = null, ?Tax_Price $tax_helper = null)
    {
        $this->product_configuration = $product_configuration;
        $this->pricing_helper = $pricing_helper;
        $this->escaper = $escaper;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Json::class);
        $this->tax_helper = $tax_helper ?? Object_Manager::get_instance()->get(Tax_Price::class);
        parent::__construct($context);
    }
    /**
     * Get selection quantity
     *
     * @param Product $product
     * @param int $selectionId
     * @return float
     */
    public function get_selection_qty(Product $product, $selection_id)
    {
        $selection_qty = $product->get_custom_option('selection_qty_' . $selection_id);
        if ($selection_qty) {
            return $selection_qty->get_value();
        }
        return 0;
    }
    /**
     * Obtain final price of selection in a bundle product
     *
     * @param ItemInterface $item
     * @param Product $selectionProduct
     * @return float
     */
    public function get_selection_final_price(Item_Interface $item, Product $selection_product)
    {
        $selection_product->unset_data('final_price');
        $product = $item->get_product();
        /** @var Price $price */
        $price = $product->get_price_model();
        return $price->get_selection_final_total_price($product, $selection_product, $item->get_qty(), $this->get_selection_qty($product, $selection_product->get_selection_id()), false, true);
    }
    /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @param ItemInterface $item
     * @return array
     */
    public function get_bundle_options(Item_Interface $item)
    {
        $options = [];
        $product = $item->get_product();
        /** @var Type $typeInstance */
        $type_instance = $product->get_type_instance();
        // get bundle options
        $options_quote_item_option = $item->get_option_by_code('bundle_option_ids');
        $bundle_options_ids = $options_quote_item_option ? $this->serializer->unserialize($options_quote_item_option->get_value()) : [];
        if ($bundle_options_ids) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
            $options_collection = $type_instance->get_options_by_ids($bundle_options_ids, $product);
            // get and add bundle selections collection
            $selections_quote_item_option = $item->get_option_by_code('bundle_selection_ids');
            $bundle_selection_ids = $this->serializer->unserialize($selections_quote_item_option->get_value());
            if (!empty($bundle_selection_ids)) {
                $selections_collection = $type_instance->get_selections_by_ids($bundle_selection_ids, $product);
                $bundle_options = $options_collection->append_selections($selections_collection, true);
                foreach ($bundle_options as $bundle_option) {
                    if ($bundle_option->get_selections()) {
                        $option = ['label' => $bundle_option->get_title(), 'value' => []];
                        $bundle_selections = $bundle_option->get_selections();
                        foreach ($bundle_selections as $bundle_selection) {
                            $option = $this->get_option_price_html($item, $bundle_selection, $option);
                        }
                        if ($option['value']) {
                            $options[] = $option;
                        }
                    }
                }
            }
        }
        return $options;
    }
    /**
     * Get bundle options' prices
     *
     * @param ItemInterface $item
     * @param ProductInterface $bundleSelection
     * @param array $option
     * @return array
     * @throws LocalizedException
     */
    private function get_option_price_html(Item_Interface $item, Product_Interface $bundle_selection, array $option): array
    {
        $product = $item->get_product();
        $qty = $this->get_selection_qty($item->get_product(), $bundle_selection->get_selection_id()) * 1;
        if ($qty) {
            $selection_price = $this->get_selection_final_price($item, $bundle_selection);
            $display_cart_prices_both = $this->tax_helper->display_cart_prices_both();
            if ($display_cart_prices_both) {
                $selection_final_price = $this->tax_helper->get_tax_price($product, $selection_price, true);
                $selection_final_price_excl_tax = $this->tax_helper->get_tax_price($product, $selection_price, false);
            } else {
                $selection_final_price = $this->tax_helper->get_tax_price($item->get_product(), $selection_price);
            }
            $option['value'][] = $qty . ' x ' . $this->escaper->escape_html($bundle_selection->get_name()) . ' ' . $this->pricing_helper->currency($selection_final_price) . ($display_cart_prices_both ? ' ' . __('Excl. tax:') . ' ' . $this->pricing_helper->currency($selection_final_price_excl_tax) : '');
            $option['has_html'] = true;
        }
        return $option;
    }
    /**
     * Retrieves product options list
     *
     * @param ItemInterface $item
     * @return array
     */
    public function get_options(Item_Interface $item)
    {
        return array_merge($this->get_bundle_options($item), $this->product_configuration->get_custom_options($item));
    }
}