<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Import_Export\Plugin\Import\Product;

use Magento\Bundle\Model\Inventory\Change_Parent_Stock_Status;
use Magento\Catalog_Import_Export\Model\Stock_Item_Importer_Interface;
/**
 * Update bundle products stock item status based on children products stock status after import
 */
class Update_Bundle_Products_Stock_Item_Status_Plugin
{
    /**
     * @var ChangeParentStockStatus
     */
    private $change_parent_stock_status;
    /**
     * @param ChangeParentStockStatus $changeParentStockStatus
     */
    public function __construct(Change_Parent_Stock_Status $change_parent_stock_status)
    {
        $this->change_parent_stock_status = $change_parent_stock_status;
    }
    /**
     * Update bundle products stock item status based on children products stock status after import
     *
     * @param StockItemImporterInterface $subject
     * @param mixed $result
     * @param array $stockData
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_import(Stock_Item_Importer_Interface $subject, $result, array $stock_data): void
    {
        if ($stock_data) {
            $this->change_parent_stock_status->execute(array_column($stock_data, 'product_id'));
        }
    }
}