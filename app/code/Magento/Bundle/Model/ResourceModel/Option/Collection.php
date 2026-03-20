<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Resource_Model\Option;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
/**
 * Bundle Options Resource Collection
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\Resource_Model\Db\Collection\Abstract_Collection
{
    /**
     * All item ids cache
     *
     * @var array
     */
    protected $_item_ids;
    /**
     * True when selections appended
     *
     * @var bool
     */
    protected $_selections_appended = false;
    /**
     * Init model and resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Bundle\Model\Option::class, \Magento\Bundle\Model\Resource_Model\Option::class);
    }
    /**
     * Joins values to options
     *
     * @param int $storeId
     * @return $this
     */
    public function join_values($store_id)
    {
        $this->get_select()->join_left(['option_value_default' => $this->get_table('catalog_product_bundle_option_value')], implode(' AND ', ['main_table.option_id = option_value_default.option_id', 'main_table.parent_id = option_value_default.parent_product_id', 'option_value_default.store_id = 0']), [])->columns(['default_title' => 'option_value_default.title']);
        $title = $this->get_connection()->get_check_sql('option_value.title IS NOT NULL', 'option_value.title', 'option_value_default.title');
        if ($store_id !== null) {
            $this->get_select()->columns(['title' => $title])->join_left(['option_value' => $this->get_table('catalog_product_bundle_option_value')], $this->get_connection()->quote_into(implode(' AND ', ['main_table.option_id = option_value.option_id', 'main_table.parent_id = option_value.parent_product_id', 'option_value.store_id = ?']), $store_id), []);
        }
        return $this;
    }
    /**
     * Sets product id filter
     *
     * @param int $productId
     * @return $this
     */
    public function set_product_id_filter($product_id)
    {
        $product_table = $this->get_table('catalog_product_entity');
        $link_field = $this->get_connection()->get_auto_increment_field($product_table);
        $this->get_select()->join(['cpe' => $product_table], 'cpe.' . $link_field . ' = main_table.parent_id', [])->where('cpe.entity_id = ?', $product_id);
        return $this;
    }
    /**
     * Set product link filter
     *
     * @param int $productLinkFieldValue
     *
     * @return $this
     * @since 100.1.0
     */
    public function set_product_link_filter($product_link_field_value)
    {
        $this->get_select()->where('main_table.parent_id = ?', $product_link_field_value);
        return $this;
    }
    /**
     * Sets order by position
     *
     * @return $this
     */
    public function set_position_order()
    {
        $this->get_select()->order('main_table.position asc')->order('main_table.option_id asc');
        return $this;
    }
    /**
     * Append selection to options
     *
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $stripBefore indicates to reload
     * @param bool $appendAll indicates do we need to filter by saleable and required custom options
     * @return \Magento\Framework\DataObject[]
     */
    public function append_selections($selections_collection, $strip_before = false, $append_all = true)
    {
        if ($strip_before) {
            $this->_strip_selections();
        }
        if (!$this->_selections_appended) {
            foreach ($selections_collection->get_items() as $key => $selection) {
                $option = $this->get_item_by_id($selection->get_option_id());
                if ($option) {
                    if ($append_all || (int) $selection->get_status() === Status::STATUS_ENABLED && !$selection->get_required_options()) {
                        $selection->set_option($option);
                        $option->add_selection($selection);
                    } else {
                        $selections_collection->remove_item_by_key($key);
                    }
                }
            }
            $this->_selections_appended = true;
        }
        return $this->get_items();
    }
    /**
     * Removes appended selections before
     *
     * @return $this
     */
    protected function _strip_selections()
    {
        foreach ($this->get_items() as $option) {
            $option->set_selections([]);
        }
        $this->_selections_appended = false;
        return $this;
    }
    /**
     * Sets filter by option id
     *
     * @param array|int $ids
     * @return $this
     */
    public function set_id_filter($ids)
    {
        if (is_array($ids)) {
            $this->add_field_to_filter('main_table.option_id', ['in' => $ids]);
        } elseif ($ids != '') {
            $this->add_field_to_filter('main_table.option_id', $ids);
        }
        return $this;
    }
    /**
     * Reset all item ids cache
     *
     * @return $this
     */
    public function reset_all_ids()
    {
        $this->_item_ids = null;
        return $this;
    }
    /**
     * Retrieve all ids for collection
     *
     * @return array
     */
    public function get_all_ids()
    {
        if ($this->_item_ids === null) {
            $this->_item_ids = parent::get_all_ids();
        }
        return $this->_item_ids;
    }
}