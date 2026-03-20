<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Resource_Model\Indexer;

use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\DB\Select;
/**
 * Class StockStatusSelectBuilder
 * Is used to create Select object that is used for Bundle product stock status indexation
 *
 * @see \Magento\Bundle\Model\ResourceModel\Indexer\Stock::_getStockStatusSelect
 */
class Stock_Status_Select_Builder
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
     * @var \Magento\Eav\Model\Config
     */
    private $eav_config;
    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(\Magento\Framework\App\Resource_Connection $resource_connection, \Magento\Framework\Entity_Manager\Metadata_Pool $metadata_pool, \Magento\Eav\Model\Config $eav_config)
    {
        $this->resource_connection = $resource_connection;
        $this->metadata_pool = $metadata_pool;
        $this->eav_config = $eav_config;
    }
    /**
     * @param Select $select
     * @return Select
     * @throws \Exception
     */
    public function build_select(Select $select)
    {
        $select = clone $select;
        $metadata = $this->metadata_pool->get_metadata(Product_Interface::class);
        $link_field = $metadata->get_link_field();
        $select->reset(Select::COLUMNS)->columns(['e.entity_id', 'cis.website_id', 'cis.stock_id'])->join_left(['o' => $this->resource_connection->get_table_name('catalog_product_bundle_stock_index')], 'o.entity_id = e.entity_id AND o.website_id = cis.website_id AND o.stock_id = cis.stock_id', [])->join_inner(['cpr' => $this->resource_connection->get_table_name('catalog_product_relation')], 'e.' . $link_field . ' = cpr.parent_id', [])->columns(['qty' => new \Zend_Db_Expr('0')]);
        if ($metadata->get_identifier_field() === $metadata->get_link_field()) {
            $select->join_inner(['cpei' => $this->resource_connection->get_table_name('catalog_product_entity_int')], 'cpr.child_id = cpei.' . $link_field . ' AND cpei.attribute_id = ' . $this->get_attribute('status')->get_id() . ' AND cpei.value = ' . Product_Status::STATUS_ENABLED, []);
        } else {
            $select->join_inner(['cpel' => $this->resource_connection->get_table_name('catalog_product_entity')], 'cpel.entity_id = cpr.child_id', [])->join_inner(['cpei' => $this->resource_connection->get_table_name('catalog_product_entity_int')], 'cpel.' . $link_field . ' = cpei.' . $link_field . ' AND cpei.attribute_id = ' . $this->get_attribute('status')->get_id() . ' AND cpei.value = ' . Product_Status::STATUS_ENABLED, []);
        }
        return $select;
    }
    /**
     * Retrieve catalog_product attribute instance by attribute code
     *
     * @param string $attributeCode
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    private function get_attribute($attribute_code)
    {
        return $this->eav_config->get_attribute(\Magento\Catalog\Model\Product::ENTITY, $attribute_code);
    }
}