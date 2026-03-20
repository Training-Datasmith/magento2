<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

use Magento\Framework\Cache\Frontend_Interface;
use Magento\Framework\Currency\Exception\Currency_Exception;
/**
 * @api
 * @since 100.0.2
 */
interface Currency_Interface
{
    /**
     * Returns a localized currency string
     *
     * @param  int|float $value OPTIONAL Currency value
     * @param  array $options OPTIONAL options to set temporary
     * @throws CurrencyException When the value is not a number
     * @return string
     */
    public function to_currency($value = null, array $options = []);
    /**
     * Set the formatting options.
     *
     * Sets the formatting options of the localized currency string
     * If no parameter is passed, the standard setting of the
     * actual set locale will be used
     *
     * @param  array $options (Optional) Options to set
     * @return CurrencyInterface
     */
    public function set_format(array $options = []);
    /**
     * Returns the actual or details of other currency symbols, when no symbol is available it returns the shortname.
     *
     * @param  string $currency OPTIONAL Currency name
     * @param  string $locale OPTIONAL Locale to display informations
     * @return string
     */
    public function get_symbol($currency = null, $locale = null);
    /**
     * Returns the actual or details of other currency shortnames
     *
     * @param  string $currency OPTIONAL Currency's name
     * @param  string $locale OPTIONAL The locale
     * @return string
     */
    public function get_short_name($currency = null, $locale = null);
    /**
     * Returns the actual or details of other currency names
     *
     * @param  string $currency OPTIONAL Currency's short name
     * @param  string $locale OPTIONAL The locale
     * @return string
     */
    public function get_name($currency = null, $locale = null);
    /**
     * Returns a list of regions where this currency is or was known
     *
     * @param  string $currency OPTIONAL Currency's short name
     * @throws CurrencyException When no currency was defined
     * @return array List of regions
     */
    public function get_region_list($currency = null);
    /**
     * Return currency list.
     *
     * Returns a list of currencies which are used in this region
     * a region name should be 2 charachters only (f.e. EG, DE, US)
     * If no region is given, the actual region is used
     *
     * @param  string $region OPTIONAL Region to return the currencies for
     * @return array List of currencies
     */
    public function get_currency_list($region = null);
    /**
     * Returns the actual currency name
     *
     * @return string
     */
    public function to_string();
    /**
     * Returns the set cache
     *
     * @return FrontendInterface|null The set cache
     */
    public static function get_cache();
    /**
     * Sets a cache for \Magento\Framework\Currency
     *
     * @param  FrontendInterface $cache Cache to set
     * @return void
     */
    public static function set_cache(Frontend_Interface $cache);
    /**
     * Returns true when a cache is set
     *
     * @return boolean
     */
    public static function has_cache();
    /**
     * Removes any set cache
     *
     * @return void
     */
    public static function remove_cache();
    /**
     * Clears all set cache data
     *
     * @param string $tag Tag to clear when the default tag name is not used
     * @return void
     */
    public static function clear_cache($tag = null);
    /**
     * Sets a new locale for data retrievement
     * Example: 'de_XX' will be set to 'de' because 'de_XX' does not exist
     * 'xx_YY' will be set to 'root' because 'xx' does not exist
     *
     * @param  string $locale OPTIONAL Locale for parsing input
     * @throws CurrencyException When the given locale does not exist
     * @return $this
     */
    public function set_locale($locale = null);
    /**
     * Returns the actual set locale
     *
     * @return string
     */
    public function get_locale();
    /**
     * Returns the value
     *
     * @return float
     */
    public function get_value();
    /**
     * Adds a currency
     *
     * @param float|int|CurrencyInterface $value Add this value to currency
     * @param string|CurrencyInterface $currency The currency to add
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function set_value($value, $currency = null);
    /**
     * Adds a currency
     *
     * @param float|int|CurrencyInterface $value Add this value to currency
     * @param string|CurrencyInterface $currency The currency to add
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function add($value, $currency = null);
    /**
     * Substracts a currency
     *
     * @param float|int|CurrencyInterface $value Substracts this value from currency
     * @param string|CurrencyInterface $currency The currency to substract
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function sub($value, $currency = null);
    /**
     * Divides a currency
     *
     * @param float|int|CurrencyInterface $value Divides this value from currency
     * @param string|CurrencyInterface $currency The currency to divide
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function div($value, $currency = null);
    /**
     * Multiplies a currency
     *
     * @param float|int|CurrencyInterface $value Multiplies this value from currency
     * @param string|CurrencyInterface $currency The currency to multiply
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function mul($value, $currency = null);
    /**
     * Calculates the modulo from a currency
     *
     * @param float|int|CurrencyInterface $value Calculate modulo from this value
     * @param string|CurrencyInterface $currency The currency to calculate the modulo
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function mod($value, $currency = null);
    /**
     * Compares two currencies
     *
     * @param float|int|CurrencyInterface $value Compares the currency with this value
     * @param string|CurrencyInterface $currency The currency to compare this value from
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function compare($value, $currency = null);
    /**
     * Returns true when the two currencies are equal
     *
     * @param float|int|CurrencyInterface $value Compares the currency with this value
     * @param string|CurrencyInterface $currency The currency to compare this value from
     * @return boolean
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function equals($value, $currency = null);
    /**
     * Returns true when the currency is more than the given value
     *
     * @param float|int|CurrencyInterface $value Compares the currency with this value
     * @param string|CurrencyInterface $currency The currency to compare this value from
     * @return boolean
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function is_more($value, $currency = null);
    /**
     * Returns true when the currency is less than the given value
     *
     * @param float|int|CurrencyInterface $value Compares the currency with this value
     * @param string|CurrencyInterface $currency The currency to compare this value from
     * @return boolean
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function is_less($value, $currency = null);
}