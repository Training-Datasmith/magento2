<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Interface CategoryProductSearchResultInterface
 * @api
 * @since 101.0.0
 */
interface Category_Product_Search_Result_Interface extends \Magento\Framework\Api\Search_Results_Interface
{
    /**
     * Get category product sets list.
     *
     * @return \Magento\Catalog\Api\Data\CategoryProductLinkInterface[]
     * @since 101.0.0
     */
    public function get_items();
    /**
     * Set category product sets list.
     *
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterface[] $items
     * @return $this
     * @since 101.0.0
     */
    public function set_items(array $items);
}