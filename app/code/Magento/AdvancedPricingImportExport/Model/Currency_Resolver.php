<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Advanced_Pricing_Import_Export\Model;

use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Store\Model\Store_Manager_Interface;
/**
 * Currency resolver for tier price scope
 */
class Currency_Resolver
{
    /**
     * @var string
     */
    private $default_base_currency;
    /**
     * Associative array with website code as the key and base currency as the value
     */
    private ?array $websites_base_currency = null;
    public function __construct(private readonly Store_Manager_Interface $store_manager, private readonly Directory_Data $directory_data)
    {
    }
    /**
     * Get base currency for all websites
     *
     * @return array associative array with website code as the key and base currency as the value
     */
    public function get_websites_base_currency(): array
    {
        if ($this->websites_base_currency === null) {
            $this->websites_base_currency = [];
            foreach ($this->store_manager->get_websites() as $website) {
                $this->websites_base_currency[$website->get_code()] = $website->get_base_currency_code();
            }
        }
        return $this->websites_base_currency;
    }
    /**
     * Get default scope base currency
     */
    public function get_default_base_currency(): string
    {
        if ($this->default_base_currency === null) {
            $this->default_base_currency = $this->directory_data->get_base_currency_code();
        }
        return $this->default_base_currency;
    }
}