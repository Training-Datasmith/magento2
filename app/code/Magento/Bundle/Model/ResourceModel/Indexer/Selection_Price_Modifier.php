<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Resource_Model\Indexer;

use Magento\Catalog_Inventory\Api\Stock_Configuration_Interface;
use Magento\Framework\App\Resource_Connection;
class Selection_Price_Modifier implements Selection_Price_Modifier_Interface
{
    /**
     * @param ResourceConnection $resource
     * @param StockConfigurationInterface $stockConfiguration
     * @param string $connectionName
     */
    public function __construct(private readonly Resource_Connection $resource, private readonly Stock_Configuration_Interface $stock_configuration, private readonly string $connection_name = 'indexer')
    {
    }
    /**
     * @inheritDoc
     */
    public function modify(string $index_table, array $dimensions): void
    {
        if (!$this->stock_configuration->is_show_out_of_stock()) {
            return;
        }
        $connection = $this->resource->get_connection($this->connection_name);
        $stock_index_table_name = $this->get_table('cataloginventory_stock_status');
        $select = $connection->select()->from(['i' => $index_table])->join_inner(['selection' => $this->get_table('catalog_product_bundle_selection')], 'selection.selection_id = i.selection_id', [])->join_inner(['child_stock' => $stock_index_table_name], 'child_stock.product_id = selection.product_id', [])->join_inner(['parent_stock' => $stock_index_table_name], 'parent_stock.product_id = i.entity_id', [])->where('parent_stock.stock_status = 1')->where('child_stock.stock_status = 0');
        $connection->query($connection->delete_from_select($select, 'i'));
    }
    /**
     * Returns fully qualified table name
     *
     * @param string $tableName
     * @return string
     */
    private function get_table(string $table_name): string
    {
        return $this->resource->get_table_name($table_name, $this->connection_name);
    }
}