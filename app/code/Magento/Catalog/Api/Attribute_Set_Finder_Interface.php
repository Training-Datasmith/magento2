<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api;

/**
 * Interface AttributeSetFinderInterface
 * @api
 * @since 101.0.0
 */
interface Attribute_Set_Finder_Interface
{
    /**
     * Get attribute set ids by product ids
     *
     * @param array $productIds
     * @return array
     * @since 101.0.0
     */
    public function find_attribute_set_ids_by_product_ids(array $product_ids);
}