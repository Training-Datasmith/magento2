<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer currency
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
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
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_locale_currency;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\Locale\Currency_Interface $locale_currency, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_locale_currency = $locale_currency;
    }
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        if ($data = $this->_get_value($row)) {
            $currency_code = $this->_get_currency_code($row);
            if (!$currency_code) {
                return $data;
            }
            $data = (float) $data * $this->_get_rate($row);
            $data = sprintf('%f', $data);
            $data = $this->_locale_currency->get_currency($currency_code)->to_currency($data);
            return $data;
        }
        return $this->get_column()->get_default();
    }
    /**
     * Returns currency code for the row, false on error
     *
     * @param \Magento\Framework\DataObject $row
     * @return string|false
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
        return false;
    }
    /**
     * Returns rate for the row, 1 by default
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
        return 1;
    }
    /**
     * Renders CSS
     *
     * @return string
     */
    public function render_css()
    {
        return parent::render_css() . ' col-price';
    }
}