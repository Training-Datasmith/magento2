<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface Category_Attribute_Search_Results_Interface extends \Magento\Framework\Api\Search_Results_Interface
{
    /**
     * Get attributes list.
     *
     * @return \Magento\Catalog\Api\Data\CategoryAttributeInterface[]
     */
    public function get_items();
    /**
     * Set attributes list.
     *
     * @param \Magento\Catalog\Api\Data\CategoryAttributeInterface[] $items
     * @return $this
     */
    public function set_items(array $items);
}