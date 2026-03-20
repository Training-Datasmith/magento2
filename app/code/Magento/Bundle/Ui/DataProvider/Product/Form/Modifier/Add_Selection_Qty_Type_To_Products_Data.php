<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Ui\Data_Provider\Product\Form\Modifier;

use Magento\Catalog_Inventory\Model\Stock_Registry_Preloader;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Ui\Data_Provider\Modifier\Modifier_Interface;
/**
 * Affects Qty field for newly added selection
 */
class Add_Selection_Qty_Type_To_Products_Data implements Modifier_Interface
{
    /**
     * @var StockRegistryPreloader
     */
    private Stock_Registry_Preloader $stock_registry_preloader;
    /**
     * Initializes dependencies
     *
     * @param StockRegistryPreloader $stockRegistryPreloader
     */
    public function __construct(Stock_Registry_Preloader $stock_registry_preloader)
    {
        $this->stock_registry_preloader = $stock_registry_preloader;
    }
    /**
     * Modify Meta
     *
     * @param array $meta
     * @return array
     */
    public function modify_meta(array $meta)
    {
        return $meta;
    }
    /**
     * Modify Data - checks if new selection can have decimal quantity
     *
     * @param array $data
     * @return array
     * @throws NoSuchEntityException
     */
    public function modify_data(array $data): array
    {
        $product_ids = array_column($data['items'], 'entity_id');
        $stock_items = [];
        if ($product_ids) {
            $stock_items = $this->stock_registry_preloader->preload_stock_items($product_ids);
        }
        $is_qty_decimals = [];
        foreach ($stock_items as $stock_item) {
            $is_qty_decimals[$stock_item->get_product_id()] = $stock_item->get_is_qty_decimal();
        }
        foreach ($data['items'] as &$item) {
            if (isset($is_qty_decimals[$item['entity_id']])) {
                $item['selection_qty_is_integer'] = !$is_qty_decimals[$item['entity_id']];
            }
        }
        return $data;
    }
}