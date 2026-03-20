<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Dashboard;

use Magento\Directory\Model\Currency;
use Magento\Store\Model\Store;
/**
 * Adminhtml dashboard bar block
 */
class Bar extends \Magento\Backend\Block\Dashboard\Abstract_Dashboard
{
    /**
     * @var array
     */
    protected $_totals = [];
    /**
     * @var Currency|null
     */
    protected $_current_currency_code = null;
    /**
     * @var Currency
     */
    private $_currency;
    /**
     * Get totals
     *
     * @return array
     */
    public function get_totals()
    {
        return $this->_totals;
    }
    /**
     * Add total
     *
     * @param string $label
     * @param float $value
     * @param bool $isQuantity
     * @return $this
     */
    public function add_total($label, $value, $is_quantity = false)
    {
        if (!$is_quantity) {
            $value = $this->format($value);
        }
        $decimals = '';
        $this->_totals[] = ['label' => $label, 'value' => $value, 'decimals' => $decimals];
        return $this;
    }
    /**
     * Formatting value specific for this store
     *
     * @param float $price
     * @return string
     */
    public function format($price)
    {
        return $this->get_currency()->format($price);
    }
    /**
     * Setting currency model
     *
     * @param Currency $currency
     * @return void
     */
    public function set_currency($currency)
    {
        $this->_currency = $currency;
    }
    /**
     * Retrieve currency model if not set then return currency model for current store
     *
     * @return Currency
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function get_currency()
    {
        if ($this->_current_currency_code === null) {
            if ($this->get_request()->get_param('store')) {
                $this->_current_currency_code = $this->_store_manager->get_store($this->get_request()->get_param('store'))->get_base_currency();
            } elseif ($this->get_request()->get_param('website')) {
                $this->_current_currency_code = $this->_store_manager->get_website($this->get_request()->get_param('website'))->get_base_currency();
            } elseif ($this->get_request()->get_param('group')) {
                $this->_current_currency_code = $this->_store_manager->get_group($this->get_request()->get_param('group'))->get_website()->get_base_currency();
            } else {
                $this->_current_currency_code = $this->_store_manager->get_store(Store::DEFAULT_STORE_ID)->get_base_currency();
            }
        }
        return $this->_current_currency_code;
    }
}