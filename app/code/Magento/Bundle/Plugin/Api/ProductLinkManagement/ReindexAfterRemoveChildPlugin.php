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
 * Reindex bundle product after child has been removed.
 */
class Reindex_After_Remove_Child_Plugin
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
     * Reindex bundle product after child has been removed.
     *
     * @param ProductLinkManagementInterface $subject
     * @param bool $result
     * @param string $sku
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_remove_child(Product_Link_Management_Interface $subject, bool $result, string $sku): bool
    {
        $bundle_product = $this->product_repository->get($sku, true);
        $this->indexer->execute_row($bundle_product->get_id());
        return $result;
    }
}