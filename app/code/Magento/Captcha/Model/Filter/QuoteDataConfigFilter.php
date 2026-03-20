<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Captcha\Model\Filter;

use Magento\Captcha\Api\Captcha_Config_Post_Processor_Interface;
/**
 * Class QuoteDataConfigFilter used for filtering config quote data based on filter list
 */
class Quote_Data_Config_Filter implements Captcha_Config_Post_Processor_Interface
{
    /**
     * @var array $filterList
     */
    private $filter_list;
    /**
     * @param array $filterList
     */
    public function __construct(array $filter_list = [])
    {
        $this->filter_list = $filter_list;
    }
    /**
     * Filters the quote config with values from a filter list
     *
     * @param array $config
     * @return array
     */
    public function process(array $config): array
    {
        foreach ($this->filter_list as $filter_key) {
            /** @var string $filterKey */
            if (isset($config['quoteData']) && array_key_exists($filter_key, $config['quoteData'])) {
                unset($config['quoteData'][$filter_key]);
            }
        }
        return $config;
    }
}