<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\Group_Repository_Interface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Store\Api\Data\Store_Interface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Store_Manager_Interface;
use Magento\Tax\Api\Data\Quote_Details_Interface_Factory;
use Magento\Tax\Api\Data\Quote_Details_Item_Interface_Factory;
use Magento\Tax\Api\Data\Tax_Class_Key_Interface;
use Magento\Tax\Api\Data\Tax_Class_Key_Interface_Factory;
use Magento\Tax\Api\Tax_Calculation_Interface;
use Magento\Tax\Model\Config;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Tax_Price
{
    /**
     * @var StoreManagerInterface
     */
    private $store_manager;
    /**
     * @var TaxClassKeyInterfaceFactory
     */
    private $tax_class_key_factory;
    /**
     * @var Config
     */
    private $tax_config;
    /**
     * @var QuoteDetailsInterfaceFactory
     */
    private $quote_details_factory;
    /**
     * @var QuoteDetailsItemInterfaceFactory
     */
    private $quote_details_item_factory;
    /**
     * @var CustomerSession
     */
    private $customer_session;
    /**
     * @var TaxCalculationInterface
     */
    private $tax_calculation_service;
    /**
     * @var GroupRepositoryInterface
     */
    private $customer_group_repository;
    /**
     * @var Session
     */
    private $checkout_session;
    /**
     * @param StoreManagerInterface $storeManager
     * @param TaxClassKeyInterfaceFactory $taxClassKeyFactory
     * @param Config $taxConfig
     * @param QuoteDetailsInterfaceFactory $quoteDetailsFactory
     * @param QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory
     * @param TaxCalculationInterface $taxCalculationService
     * @param CustomerSession $customerSession
     * @param GroupRepositoryInterface $customerGroupRepository
     * @param Session $checkoutSession
     */
    public function __construct(Store_Manager_Interface $store_manager, Tax_Class_Key_Interface_Factory $tax_class_key_factory, Config $tax_config, Quote_Details_Interface_Factory $quote_details_factory, Quote_Details_Item_Interface_Factory $quote_details_item_factory, Tax_Calculation_Interface $tax_calculation_service, Customer_Session $customer_session, Group_Repository_Interface $customer_group_repository, Session $checkout_session)
    {
        $this->store_manager = $store_manager;
        $this->tax_class_key_factory = $tax_class_key_factory;
        $this->tax_config = $tax_config;
        $this->quote_details_factory = $quote_details_factory;
        $this->quote_details_item_factory = $quote_details_item_factory;
        $this->tax_calculation_service = $tax_calculation_service;
        $this->customer_session = $customer_session;
        $this->customer_group_repository = $customer_group_repository;
        $this->checkout_session = $checkout_session;
    }
    /**
     * Get product price with all tax settings processing for cart
     *
     * @param Product $product
     * @param float $price
     * @param bool|null $includingTax
     * @param int|null $ctc
     * @param Store|bool|int|string|null $store
     * @param bool|null $priceIncludesTax
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function get_tax_price(Product $product, float $price, ?bool $including_tax = null, ?int $ctc = null, Store|bool|int|string|null $store = null, ?bool $price_includes_tax = null): float
    {
        if (!$price) {
            return $price;
        }
        $store = $this->store_manager->get_store($store);
        $store_id = $store?->get_id();
        $tax_class_key = $this->tax_class_key_factory->create();
        $customer_tax_class_key = $this->tax_class_key_factory->create();
        $item = $this->quote_details_item_factory->create();
        $quote_details = $this->quote_details_factory->create();
        $customer_quote = $this->checkout_session->get_quote();
        if ($price_includes_tax === null) {
            $price_includes_tax = $this->tax_config->price_includes_tax($store);
        }
        $tax_class_key->set_type(Tax_Class_Key_Interface::TYPE_ID)->set_value($product->get_tax_class_id());
        if ($ctc === null && $this->customer_session->get_customer_group_id() != null) {
            $ctc = $this->customer_group_repository->get_by_id($this->customer_session->get_customer_group_id())->get_tax_class_id();
        }
        $customer_tax_class_key->set_type(Tax_Class_Key_Interface::TYPE_ID)->set_value($ctc);
        $item->set_quantity(1)->set_code($product->get_sku())->set_short_description($product->get_short_description())->set_tax_class_key($tax_class_key)->set_is_tax_included($price_includes_tax)->set_type('product')->set_unit_price($price);
        $quote_details->set_shipping_address($customer_quote->get_shipping_address()->get_data_model())->set_customer_tax_class_key($customer_tax_class_key)->set_items([$item])->set_customer_id($this->customer_session->get_customer_id());
        $tax_details = $this->tax_calculation_service->calculate_tax($quote_details, $store_id);
        $items = $tax_details->get_items();
        $tax_details_item = array_shift($items);
        if ($including_tax !== null) {
            if ($including_tax) {
                $price = $tax_details_item->get_price_incl_tax();
            } else {
                $price = $tax_details_item->get_price();
            }
        } else {
            $price = $this->tax_config->display_cart_prices_excl_tax($store) || $this->tax_config->display_cart_prices_both($store) ? $tax_details_item->get_price() : $tax_details_item->get_price_incl_tax();
        }
        return $price;
    }
    /**
     * Check if both cart prices are shown
     *
     * @param StoreInterface|null $store
     * @return bool
     */
    public function display_cart_prices_both(?Store_Interface $store = null): bool
    {
        return $this->tax_config->display_cart_prices_both($store);
    }
}