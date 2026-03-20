<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Inventory;

use Magento\Catalog\Api\Data\Product_Interface as Product;
use Magento\Catalog_Inventory\Observer\Parent_Item_Processor_Interface;
/**
 * Bundle product stock item processor
 */
class Parent_Item_Processor implements Parent_Item_Processor_Interface
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
     * @inheritdoc
     */
    public function process(Product $product)
    {
        $this->change_parent_stock_status->execute([$product->get_id()]);
    }
}