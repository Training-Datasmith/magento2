<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Inventory;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog_Inventory\Api\Data\Stock_Item_Interface;
use Magento\Catalog_Inventory\Api\Stock_Configuration_Interface;
use Magento\Catalog_Inventory\Api\Stock_Item_Criteria_Interface_Factory;
use Magento\Catalog_Inventory\Api\Stock_Item_Repository_Interface;
/***
 * Update stock status of bundle products based on children products stock status
 */
class Change_Parent_Stock_Status
{
    /**
     * @var Type
     */
    private $bundle_type;
    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $criteria_interface_factory;
    /**
     * @var StockItemRepositoryInterface
     */
    private $stock_item_repository;
    /**
     * @var StockConfigurationInterface
     */
    private $stock_configuration;
    /**
     * @param StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockConfigurationInterface $stockConfiguration
     * @param Type $bundleType
     */
    public function __construct(Stock_Item_Criteria_Interface_Factory $criteria_interface_factory, Stock_Item_Repository_Interface $stock_item_repository, Stock_Configuration_Interface $stock_configuration, Type $bundle_type)
    {
        $this->bundle_type = $bundle_type;
        $this->criteria_interface_factory = $criteria_interface_factory;
        $this->stock_item_repository = $stock_item_repository;
        $this->stock_configuration = $stock_configuration;
    }
    /**
     * Update stock status of bundle products based on children products stock status
     *
     * @param array $childrenIds
     * @return void
     */
    public function execute(array $children_ids): void
    {
        $parent_ids = $this->bundle_type->get_parent_ids_by_child($children_ids);
        foreach (array_unique($parent_ids) as $product_id) {
            $this->process_stock_for_parent((int) $product_id);
        }
    }
    /**
     * Update stock status of bundle product based on children products stock status
     *
     * @param int $productId
     * @return void
     */
    private function process_stock_for_parent(int $product_id): void
    {
        $stock_items = $this->get_stock_items([$product_id]);
        $parent_stock_item = $stock_items[$product_id] ?? null;
        if ($parent_stock_item) {
            $children_is_in_stock = $this->is_children_in_stock($product_id);
            if ($this->is_need_to_update_parent($parent_stock_item, $children_is_in_stock)) {
                $parent_stock_item->set_is_in_stock($children_is_in_stock);
                $parent_stock_item->set_stock_status_changed_auto(1);
                $this->stock_item_repository->save($parent_stock_item);
            }
        }
    }
    /**
     * Returns stock status of bundle product based on children stock status
     *
     * Returns TRUE if any of the following conditions is true:
     * - At least one product is in-stock in each required option
     * - Any product is in-stock (if all options are optional)
     *
     * @param int $productId
     * @return bool
     */
    private function is_children_in_stock(int $product_id): bool
    {
        $children_is_in_stock = false;
        $children_ids = $this->bundle_type->get_children_ids($product_id, true);
        $stock_items = $this->get_stock_items(array_merge(...array_values($children_ids)));
        foreach ($children_ids as $children_ids_per_option) {
            $children_is_in_stock = false;
            foreach ($children_ids_per_option as $id) {
                $stock_item = $stock_items[$id] ?? null;
                if ($stock_item && $stock_item->get_is_in_stock()) {
                    $children_is_in_stock = true;
                    break;
                }
            }
            if (!$children_is_in_stock) {
                break;
            }
        }
        return $children_is_in_stock;
    }
    /**
     * Check if parent item should be updated
     *
     * @param StockItemInterface $parentStockItem
     * @param bool $childrenIsInStock
     * @return bool
     */
    private function is_need_to_update_parent(Stock_Item_Interface $parent_stock_item, bool $children_is_in_stock): bool
    {
        return $parent_stock_item->get_is_in_stock() !== $children_is_in_stock && ($children_is_in_stock === false || $parent_stock_item->get_stock_status_changed_auto());
    }
    /**
     * Get stock items for provided product IDs
     *
     * @param array $productIds
     * @return StockItemInterface[]
     */
    private function get_stock_items(array $product_ids): array
    {
        $criteria = $this->criteria_interface_factory->create();
        $criteria->set_scope_filter($this->stock_configuration->get_default_scope_id());
        $criteria->set_products_filter(array_unique($product_ids));
        $stock_item_collection = $this->stock_item_repository->get_list($criteria);
        $stock_items = [];
        foreach ($stock_item_collection->get_items() as $stock_item) {
            $stock_items[$stock_item->get_product_id()] = $stock_item;
        }
        return $stock_items;
    }
}