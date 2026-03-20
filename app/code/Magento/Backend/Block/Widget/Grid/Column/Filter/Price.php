<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Range grid column filter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Abstract_Filter
{
    /**
     * @var array
     */
    protected $_currency_list = null;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency_model = null;
    /**
     * @var \Magento\Directory\Model\Currency\DefaultLocator
     */
    protected $_currency_locator = null;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Directory\Model\Currency $currencyModel
     * @param \Magento\Directory\Model\Currency\DefaultLocator $currencyLocator
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\DB\Helper $resource_helper, \Magento\Directory\Model\Currency $currency_model, \Magento\Directory\Model\Currency\Default_Locator $currency_locator, array $data = [])
    {
        parent::__construct($context, $resource_helper, $data);
        $this->_currency_model = $currency_model;
        $this->_currency_locator = $currency_locator;
    }
    /**
     * Retrieve html
     *
     * @return string
     */
    public function get_html()
    {
        $html = '<div class="range">';
        $html .= '<div class="range-line">' . '<input type="text" name="' . $this->_get_html_name() . '[from]" id="' . $this->_get_html_id() . '_from" placeholder="' . __('From') . '" value="' . $this->get_escaped_value('from') . '" class="input-text admin__control-text no-changes"  ' . $this->get_ui_id('filter', $this->_get_html_name(), 'from') . '/></div>';
        $html .= '<div class="range-line">' . '<input type="text" name="' . $this->_get_html_name() . '[to]" id="' . $this->_get_html_id() . '_to" placeholder="' . __('To') . '" value="' . $this->get_escaped_value('to') . '" class="input-text admin__control-text no-changes" ' . $this->get_ui_id('filter', $this->_get_html_name(), 'to') . '/></div>';
        if ($this->get_display_currency_select()) {
            $html .= '<div class="range-line">' . $this->_get_currency_select_html() . '</div>';
        }
        $html .= '</div>';
        return $html;
    }
    /**
     * Retrieve display currency select
     *
     * @return true|mixed
     */
    public function get_display_currency_select()
    {
        if ($this->get_column()->get_data('display_currency_select') !== null) {
            return $this->get_column()->get_data('display_currency_select');
        } else {
            return true;
        }
    }
    /**
     * Retrieve currency affect
     *
     * @return true|mixed
     */
    public function get_currency_affect()
    {
        if ($this->get_column()->get_data('currency_affect') !== null) {
            return $this->get_column()->get_data('currency_affect');
        } else {
            return true;
        }
    }
    /**
     * Retrieve currency select html
     *
     * @return string
     */
    protected function _get_currency_select_html()
    {
        $value = $this->get_escaped_value('currency');
        if (!$value) {
            $value = $this->_get_column_currency_code();
        }
        $html = '';
        $html .= '<select name="' . $this->_get_html_name() . '[currency]" id="' . $this->_get_html_id() . '_currency">';
        foreach ($this->_get_currency_list() as $currency) {
            $html .= '<option value="' . $currency . '" ' . ($currency == $value ? 'selected="selected"' : '') . '>' . $currency . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
    /**
     * Retrieve list of currencies
     *
     * @return array|null
     */
    protected function _get_currency_list()
    {
        if ($this->_currency_list === null) {
            $this->_currency_list = $this->_currency_model->get_config_allow_currencies();
        }
        return $this->_currency_list;
    }
    /**
     * Retrieve filter value
     *
     * @param string|null $index
     * @return array|null
     */
    public function get_value($index = null)
    {
        if ($index) {
            return $this->get_data('value', $index);
        }
        $value = $this->get_data('value');
        if (isset($value['from']) && strlen($value['from']) > 0 || isset($value['to']) && strlen($value['to']) > 0) {
            return $value;
        }
        return null;
    }
    /**
     * Retrieve filter condition
     *
     * @return array
     */
    public function get_condition()
    {
        $value = $this->get_value();
        if (isset($value['currency']) && $this->get_currency_affect()) {
            $display_currency = $value['currency'];
        } else {
            $display_currency = $this->_get_column_currency_code();
        }
        $rate = $this->_get_rate($display_currency, $this->_get_column_currency_code());
        if (isset($value['from'])) {
            $value['from'] = (float) $value['from'] * $rate;
        }
        if (isset($value['to'])) {
            $value['to'] = (float) $value['to'] * $rate;
        }
        $this->prepare_rates($display_currency);
        return $value;
    }
    /**
     * Retrieve column currency code
     *
     * @return string
     */
    protected function _get_column_currency_code()
    {
        return $this->get_column()->get_currency_code() ? $this->get_column()->get_currency_code() : $this->_currency_locator->get_default_currency($this->_request);
    }
    /**
     * Get currency rate
     *
     * @param string $fromRate
     * @param string $toRate
     * @return float
     */
    protected function _get_rate($from_rate, $to_rate)
    {
        return $this->_currency_model->load($from_rate)->get_any_rate($to_rate);
    }
    /**
     * Prepare currency rates
     *
     * @param string $displayCurrency
     * @return void
     */
    public function prepare_rates($display_currency)
    {
        $store_currency = $this->_get_column_currency_code();
        $rate = $this->_get_rate($store_currency, $display_currency);
        if ($rate) {
            $this->get_column()->set_rate($rate);
            $this->get_column()->set_currency_code($display_currency);
        }
    }
}