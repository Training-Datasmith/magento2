<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Resource_Model;

use Magento\Catalog\Api\Data\Category_Interface;
use Magento\Catalog\Model\Indexer\Category\Product\Abstract_Action;
use Magento\Catalog\Model\Indexer\Product\Price\Dimension_Collection_Factory;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Model\Resource_Model\Db\Abstract_Db;
use Magento\Framework\Model\Resource_Model\Db\Context;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\Index_Scope_Resolver_Interface;
use Magento\Framework\Search\Request\Index_Scope_Resolver_Interface as TableResolver;
use Magento\Store\Model\Indexer\Website_Dimension_Provider;
/**
 * @api
 * @since 100.1.0
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends Abstract_Db
{
    /**
     * @var TableResolver
     */
    private $table_resolver;
    /**
     * @var DimensionCollectionFactory|null
     */
    private $dimension_collection_factory;
    /**
     * @var int|null
     */
    private $website_id;
    /**
     * Index constructor.
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        /**
         * @since 100.1.0
         */
        protected \Magento\Store\Model\Store_Manager_Interface $store_manager,
        /**
         * @since 100.1.0
         */
        protected \Magento\Framework\Entity_Manager\Metadata_Pool $metadata_pool,
        $connection_name = null,
        ?Table_Resolver $table_resolver = null,
        ?Dimension_Collection_Factory $dimension_collection_factory = null
    )
    {
        parent::__construct($context, $connection_name);
        $this->table_resolver = $table_resolver ?: Object_Manager::get_instance()->get(Index_Scope_Resolver_Interface::class);
        $this->dimension_collection_factory = $dimension_collection_factory ?: Object_Manager::get_instance()->get(Dimension_Collection_Factory::class);
    }
    /**
     * Implementation of abstract construct
     *
     * @return void
     * @since 100.1.0
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    protected function _construct()
    {
    }
    /**
     * Return array of price data per customer and website by products
     *
     * @param null|array $productIds
     * @since 100.1.0
     */
    protected function _get_catalog_product_price_data($product_ids = null): array
    {
        $connection = $this->get_connection();
        $catalog_product_index_price_select = [];
        foreach ($this->dimension_collection_factory->create() as $dimensions) {
            if (!isset($dimensions[Website_Dimension_Provider::DIMENSION_NAME]) || $this->website_id === null || $dimensions[Website_Dimension_Provider::DIMENSION_NAME]->get_value() === $this->website_id) {
                $select = $connection->select()->from($this->table_resolver->resolve('catalog_product_index_price', $dimensions), ['entity_id', 'customer_group_id', 'website_id', 'min_price']);
                if ($product_ids) {
                    $select->where('entity_id IN (?)', $product_ids);
                }
                $catalog_product_index_price_select[] = $select;
            }
        }
        $catalog_product_index_price_union_select = $connection->select()->union($catalog_product_index_price_select);
        $result = [];
        foreach ($connection->fetch_all($catalog_product_index_price_union_select) as $row) {
            $result[$row['website_id']][$row['entity_id']][$row['customer_group_id']] = round((float) $row['min_price'], 2);
        }
        return $result;
    }
    /**
     * Retrieve price data for product
     *
     * @param null|array $productIds
     * @param int $storeId
     * @return array
     * @since 100.1.0
     */
    public function get_price_index_data($product_ids, $store_id)
    {
        $website_id = $this->store_manager->get_store($store_id)->get_website_id();
        $this->website_id = $website_id;
        $price_products_index_data = $this->_get_catalog_product_price_data($product_ids);
        $this->website_id = null;
        if (!isset($price_products_index_data[$website_id])) {
            return [];
        }
        return $price_products_index_data[$website_id];
    }
    /**
     * Prepare system index data for products.
     *
     * @param int $storeId
     * @param null|array $productIds
     * @since 100.1.0
     */
    public function get_category_product_index_data($store_id = null, $product_ids = null): array
    {
        $connection = $this->get_connection();
        $catalog_category_product_dimension = new Dimension(\Magento\Store\Model\Store::ENTITY, $store_id);
        $catalog_category_product_table_name = $this->table_resolver->resolve(Abstract_Action::MAIN_INDEX_TABLE, [$catalog_category_product_dimension]);
        $select = $connection->select()->from([$catalog_category_product_table_name], ['category_id', 'product_id', 'position', 'store_id'])->where('store_id = ?', $store_id);
        if ($product_ids) {
            $select->where('product_id IN (?)', $product_ids);
        }
        $result = [];
        foreach ($connection->fetch_all($select) as $row) {
            $result[$row['product_id']][$row['category_id']] = $row['position'];
        }
        return $result;
    }
    /**
     * Retrieve moved categories product ids
     *
     * @param int $categoryId
     * @return array
     * @since 100.1.0
     */
    public function get_moved_category_product_ids($category_id)
    {
        $connection = $this->get_connection();
        $identifier_field = $this->metadata_pool->get_metadata(Category_Interface::class)->get_identifier_field();
        $select = $connection->select()->distinct()->from(['c_p' => $this->get_table('catalog_category_product')], ['product_id'])->join(['c_e' => $this->get_table('catalog_category_entity')], 'c_p.category_id = c_e.' . $identifier_field, [])->where($connection->quote_into('c_e.path LIKE ?', '%/' . $category_id . '/%'))->or_where('c_p.category_id = ?', $category_id);
        return $connection->fetch_col($select);
    }
}