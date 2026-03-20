<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Resource_Model\Indexer;

use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Framework\DB\Select;
/**
 * Class BundleOptionStockDataSelectBuilder
 * Is used to create Select object that is used for Bundle product stock status indexation
 *
 * @see \Magento\Bundle\Model\ResourceModel\Indexer\Stock::_prepareBundleOptionStockData
 */
class Bundle_Option_Stock_Data_Select_Builder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource_connection;
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadata_pool;
    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(\Magento\Framework\App\Resource_Connection $resource_connection, \Magento\Framework\Entity_Manager\Metadata_Pool $metadata_pool)
    {
        $this->resource_connection = $resource_connection;
        $this->metadata_pool = $metadata_pool;
    }
    /**
     * Build bundle options select
     *
     * @param string $idxTable
     * @return Select
     */
    public function build_select($idx_table)
    {
        $select = $this->resource_connection->get_connection()->select();
        $link_field = $this->metadata_pool->get_metadata(Product_Interface::class)->get_link_field();
        $select->from(['product' => $this->resource_connection->get_table_name('catalog_product_entity')], ['entity_id'])->join(['bo' => $this->resource_connection->get_table_name('catalog_product_bundle_option')], "bo.parent_id = product.{$link_field}", [])->join(['cis' => $this->resource_connection->get_table_name('cataloginventory_stock')], '', ['website_id', 'stock_id'])->join_left(['bs' => $this->resource_connection->get_table_name('catalog_product_bundle_selection')], 'bs.option_id = bo.option_id', [])->join_left(['i' => $idx_table], 'i.product_id = bs.product_id AND i.website_id = cis.website_id AND i.stock_id = cis.stock_id', [])->join_left(['cisi' => $this->resource_connection->get_table_name('cataloginventory_stock_item')], 'cisi.product_id = i.product_id AND cisi.stock_id = i.stock_id', [])->join_left(['e' => $this->resource_connection->get_table_name('catalog_product_entity')], 'e.entity_id = bs.product_id', [])->group(['product.entity_id', 'cis.website_id', 'cis.stock_id', 'bo.option_id'])->columns(['option_id' => 'bo.option_id']);
        return $select;
    }
}