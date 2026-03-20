<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Plugin\Api\Product_Link_Management;

use Magento\Bundle\Api\Product_Link_Management_Interface;
use Magento\Catalog\Api\Product_Repository_Interface;
use Magento\Catalog\Model\Indexer\Product\Full;
/**
 * Reindex bundle product after child has been added.
 */
class Reindex_After_Add_Child_By_Sku_Plugin
{
    /**
     * @var Full
     */
    private $indexer;
    /**
     * @var ProductRepositoryInterface
     */
    private $product_repository;
    /**
     * @param Full $indexer
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(Full $indexer, Product_Repository_Interface $product_repository)
    {
        $this->indexer = $indexer;
        $this->product_repository = $product_repository;
    }
    /**
     * Reindex bundle product after child has been added.
     *
     * @param ProductLinkManagementInterface $subject
     * @param int $result
     * @param string $sku
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_add_child_by_product_sku(Product_Link_Management_Interface $subject, int $result, string $sku): int
    {
        $bundle_product = $this->product_repository->get($sku, true);
        $this->indexer->execute_row($bundle_product->get_id());
        return $result;
    }
}