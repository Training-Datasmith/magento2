<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\Search_Results_Interface;
/**
 * @api
 * @since 102.0.0
 */
interface Category_Search_Results_Interface extends Search_Results_Interface
{
    /**
     * Get categories
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface[]
     * @since 102.0.0
     */
    public function get_items();
    /**
     * Set categories
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface[] $items
     * @return $this
     * @since 102.0.0
     */
    public function set_items(array $items);
}