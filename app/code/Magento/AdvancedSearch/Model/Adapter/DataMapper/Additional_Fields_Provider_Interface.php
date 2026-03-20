<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Adapter\Data_Mapper;

/**
 * Provide additional fields for data mapper during search indexer
 * Must return array with the following format: [[product id] => [field name1 => value1, ...], ...]
 * @api
 * @since 100.2.0
 */
interface Additional_Fields_Provider_Interface
{
    /**
     * Get additional fields for data mapper during search indexer based on product ids and store id.
     *
     * @param int $storeId
     * @return array
     * @since 100.2.0
     */
    public function get_fields(array $product_ids, $store_id);
}