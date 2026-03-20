<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Resource_Model;

/**
 * Bundle Resource Model
 *
 * @api
 * @since 100.0.2
 */
class Bundle extends \Magento\Framework\Model\Resource_Model\Db\Abstract_Db
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Relation
     */
    protected $_product_relation;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    protected $quote_resource;
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\Relation $productRelation
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     * @param string $connectionName
     */
    public function __construct(\Magento\Framework\Model\Resource_Model\Db\Context $context, \Magento\Catalog\Model\Resource_Model\Product\Relation $product_relation, \Magento\Quote\Model\Resource_Model\Quote $quote_resource, $connection_name = null)
    {
        parent::__construct($context, $connection_name);
        $this->_product_relation = $product_relation;
        $this->quote_resource = $quote_resource;
    }
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }
    /**
     * Preparing select for getting selection's raw data by product id
     * also can be specified extra parameter for limit which columns should be selected
     *
     * @param int $productId
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function _get_select($product_id, $columns = [])
    {
        return $this->get_connection()->select()->from(['bo' => $this->get_table('catalog_product_bundle_option')], ['type', 'option_id'])->where('bo.parent_id = ?', $product_id)->where('bo.required = 1')->join_left(['bs' => $this->get_table('catalog_product_bundle_selection')], 'bs.option_id = bo.option_id AND bs.parent_product_id = bo.parent_id', $columns);
    }
    /**
     * Retrieve selection data for specified product id
     *
     * @param int $productId
     * @return array
     */
    public function get_selections_data($product_id)
    {
        return $this->get_connection()->fetch_all($this->_get_select($product_id, ['*']));
    }
    /**
     * Removing all quote items for specified product
     *
     * @param int $productId
     * @return void
     */
    public function drop_all_quote_child_items($product_id)
    {
        $connection = $this->quote_resource->get_connection();
        $select = $connection->select();
        $quote_item_ids = $connection->fetch_col($select->from($this->get_table('quote_item'), ['item_id'])->where('product_id = :product_id'), ['product_id' => $product_id]);
        if ($quote_item_ids) {
            $connection->delete($this->get_table('quote_item'), ['parent_item_id IN(?)' => $quote_item_ids]);
        }
    }
    /**
     * Removes specified selections by ids for specified product id
     *
     * @param int $productId
     * @param array $ids
     * @return void
     */
    public function drop_all_unneeded_selections($product_id, $ids)
    {
        $where = ['parent_product_id = ?' => $product_id];
        if (!empty($ids)) {
            $where['selection_id NOT IN (?) '] = $ids;
        }
        $this->get_connection()->delete($this->get_table('catalog_product_bundle_selection'), $where);
    }
    /**
     * Save product relations
     *
     * @param int $parentId
     * @param array $childIds
     * @return $this
     */
    public function save_product_relations($parent_id, $child_ids)
    {
        $this->_product_relation->process_relations($parent_id, $child_ids);
        return $this;
    }
    /**
     * Add product relation (duplicate will be updated)
     *
     * @param int $parentId
     * @param int $childId
     * @return $this
     * @since 100.1.0
     */
    public function add_product_relation($parent_id, $child_id)
    {
        $this->_product_relation->add_relation($parent_id, $child_id);
        return $this;
    }
    /**
     * Add product relations
     *
     * @param int $parentId
     * @param array $childIds
     * @return $this
     */
    public function add_product_relations($parent_id, $child_ids)
    {
        $this->_product_relation->add_relations($parent_id, $child_ids);
        return $this;
    }
    /**
     * Remove product relations
     *
     * @param int $parentId
     * @param array $childIds
     * @return $this
     */
    public function remove_product_relations($parent_id, $child_ids)
    {
        $this->_product_relation->remove_relations($parent_id, $child_ids);
        return $this;
    }
}