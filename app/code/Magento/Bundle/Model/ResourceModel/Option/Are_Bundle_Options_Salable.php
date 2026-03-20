<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Resource_Model\Option;

use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Catalog\Api\Product_Attribute_Repository_Interface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Entity_Manager\Metadata_Pool;
class Are_Bundle_Options_Salable
{
    /**
     * @var ResourceConnection
     */
    private $resource_connection;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $product_attribute_repository;
    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(Resource_Connection $resource_connection, Metadata_Pool $metadata_pool, Product_Attribute_Repository_Interface $product_attribute_repository)
    {
        $this->resource_connection = $resource_connection;
        $this->metadata_pool = $metadata_pool;
        $this->product_attribute_repository = $product_attribute_repository;
    }
    /**
     * Check are bundle product options salable
     *
     * @param int $entityId
     * @param int $storeId
     * @return bool
     */
    public function execute(int $entity_id, int $store_id): bool
    {
        $link_field = $this->metadata_pool->get_metadata(Product_Interface::class)->get_link_field();
        $connection = $this->resource_connection->get_connection();
        $options_saleability_select = $connection->select()->from(['parent_products' => $this->resource_connection->get_table_name('catalog_product_entity')], [])->join_inner(['bundle_options' => $this->resource_connection->get_table_name('catalog_product_bundle_option')], "bundle_options.parent_id = parent_products.{$link_field}", [])->join_inner(['bundle_selections' => $this->resource_connection->get_table_name('catalog_product_bundle_selection')], 'bundle_selections.option_id = bundle_options.option_id', [])->join_inner(['child_products' => $this->resource_connection->get_table_name('catalog_product_entity')], 'child_products.entity_id = bundle_selections.product_id', [])->group(['bundle_options.parent_id', 'bundle_options.option_id'])->where('parent_products.entity_id = ?', $entity_id);
        $status_attr = $this->product_attribute_repository->get(Product_Interface::STATUS);
        $options_saleability_select->join_inner(['child_status_global' => $status_attr->get_backend_table()], "child_status_global.{$link_field} = child_products.{$link_field}" . " AND child_status_global.attribute_id = {$status_attr->get_attribute_id()}" . ' AND child_status_global.store_id = 0', [])->join_left(['child_status_store' => $status_attr->get_backend_table()], "child_status_store.{$link_field} = child_products.{$link_field}" . " AND child_status_store.attribute_id = {$status_attr->get_attribute_id()}" . " AND child_status_store.store_id = {$store_id}", []);
        $is_option_salable_expr = new \Zend_Db_Expr(sprintf('MAX(IFNULL(child_status_store.value, child_status_global.value) != %s)', Product_Status::STATUS_DISABLED));
        $is_required_option_unsalable = $connection->get_check_sql('required = 1 AND ' . $is_option_salable_expr . ' = 0', '1', '0');
        $options_saleability_select->columns(['required' => 'bundle_options.required', 'is_salable' => $is_option_salable_expr, 'is_required_and_unsalable' => $is_required_option_unsalable]);
        $select = $connection->select()->from($options_saleability_select, [new \Zend_Db_Expr('(MAX(is_salable) = 1 AND MAX(is_required_and_unsalable) = 0)')]);
        $is_salable = $connection->fetch_one($select);
        return (bool) $is_salable;
    }
}