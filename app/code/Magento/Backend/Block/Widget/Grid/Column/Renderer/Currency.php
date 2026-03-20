<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\Exception\No_Such_Entity_Exception;
/**
 * Backend grid item renderer currency
 *
 * @api
 * @since 100.0.2
 */
class Currency extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @var int
     */
    protected $_default_width = 100;
    /**
     * Currency objects cache
     *
     * @var \Magento\Framework\DataObject[]
     */
    protected static $_currencies = [];
    /**
     * Application object
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_store_manager;
    /**
     * @var \Magento\Directory\Model\Currency\DefaultLocator
     */
    protected $_currency_locator;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_default_base_currency;
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_locale_currency;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\Currency\DefaultLocator $currencyLocator
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Store\Model\Store_Manager_Interface $store_manager, \Magento\Directory\Model\Currency\Default_Locator $currency_locator, \Magento\Directory\Model\Currency_Factory $currency_factory, \Magento\Framework\Locale\Currency_Interface $locale_currency, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_store_manager = $store_manager;
        $this->_currency_locator = $currency_locator;
        $this->_locale_currency = $locale_currency;
        $default_base_currency_code = $currency_locator->get_default_currency($this->_request);
        $this->_default_base_currency = $currency_factory->create()->load($default_base_currency_code);
    }
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        if ($data = (string) $this->_get_value($row)) {
            $currency_code = $this->_get_currency_code($row);
            $sign = (bool) (int) $this->get_column()->get_show_number_sign() && $data > 0 ? '+' : '';
            $data = sprintf('%f', $data);
            $data = $this->_locale_currency->get_currency($currency_code)->to_currency($data);
            return $sign . $data;
        }
        return $this->get_column()->get_default();
    }
    /**
     * Returns currency code, false on error
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    protected function _get_currency_code($row)
    {
        if ($code = $this->get_column()->get_currency_code()) {
            return $code;
        }
        $currency = $this->get_column()->get_currency();
        if ($currency !== null && $code = $row->get_data($currency)) {
            return $code;
        }
        $store_id = $row->get_data('store_id');
        if ($store_id) {
            try {
                $store = $this->_store_manager->get_store($store_id);
                // Check if the currency is set at the store level
                $currency_code = $store->get_current_currency_code();
                if ($currency_code) {
                    return $currency_code;
                }
                $website = $store->get_website();
                // Check if the currency is set at the website level
                $currency_code = $website->get_base_currency_code();
                if ($currency_code) {
                    return $currency_code;
                }
            } catch (No_Such_Entity_Exception $e) {
                $this->_logger->warning('Failed to get website currency: ' . $e->get_message());
            }
        }
        return $this->_currency_locator->get_default_currency($this->_request);
    }
    /**
     * Get rate for current row, 1 by default
     *
     * @param \Magento\Framework\DataObject $row
     * @return float|int
     */
    protected function _get_rate($row)
    {
        if ($rate = $this->get_column()->get_rate()) {
            return (float) $rate;
        }
        $rate_field = $this->get_column()->get_rate_field();
        if ($rate_field !== null && $rate = $row->get_data($rate_field)) {
            return (float) $rate;
        }
        $store_id = $row->get_data('store_id');
        if ($store_id) {
            try {
                $store = $this->_store_manager->get_store($store_id);
                return $store->get_base_currency()->get_rate($store->get_current_currency_code());
            } catch (No_Such_Entity_Exception $e) {
                $this->_logger->warning('Failed to get website currency: ' . $e->get_message());
            }
        }
        return $this->_default_base_currency->get_rate($this->_get_currency_code($row));
    }
    /**
     * Returns HTML for CSS
     *
     * @return string
     */
    public function render_css()
    {
        return parent::render_css() . ' a-right';
    }
}