<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

use Magento\Framework\App\Cache_Interface;
use Magento\Framework\Currency\Data\Currency as CurrencyData;
use Magento\Framework\Currency\Exception\Currency_Exception;
class Currency extends Currency_Data implements Currency_Interface
{
    /**
     * Creates a currency instance.
     *
     * @param CacheInterface $appCache
     * @param array|string|null $options Options array or currency short name when string is given
     * @param string|null $locale Locale name
     * @throws CurrencyException
     */
    public function __construct(Cache_Interface $app_cache, $options = null, $locale = null)
    {
        $frontend_cache = $app_cache->get_frontend();
        $low_level_cache = $frontend_cache->get_low_level_frontend();
        self::set_cache($low_level_cache);
        parent::__construct($options, $locale);
    }
}