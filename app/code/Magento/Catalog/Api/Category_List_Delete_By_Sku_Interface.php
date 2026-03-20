<?php

declare (strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 * @since 104.0.0
 */
interface Category_List_Delete_By_Sku_Interface
{
    /**
     * Delete by skus list
     *
     * @param int      $categoryId
     * @param string[] $productSkuList
     * @return bool
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @since 104.0.0
     */
    public function delete_by_skus(int $category_id, array $product_sku_list): bool;
}