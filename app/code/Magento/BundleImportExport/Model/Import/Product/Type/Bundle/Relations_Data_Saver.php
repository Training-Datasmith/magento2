<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle_Import_Export\Model\Import\Product\Type\Bundle;

use Magento\Catalog\Model\Resource_Model\Product\Relation;
use Magento\Framework\App\Object_Manager;
/**
 * A bundle product relations (options, selections, etc.) data saver.
 *
 * Performs saving of a bundle product relations data during import operations.
 */
class Relations_Data_Saver
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;
    /**
     * @var Relation
     */
    private $product_relation;
    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Relation                                  $productRelation
     */
    public function __construct(\Magento\Framework\App\Resource_Connection $resource, ?Relation $product_relation = null)
    {
        $this->resource = $resource;
        $this->product_relation = $product_relation ?: Object_Manager::get_instance()->get(Relation::class);
    }
    /**
     * Saves given options.
     *
     * @param array $options
     *
     * @return void
     */
    public function save_options(array $options)
    {
        if (!empty($options)) {
            $this->resource->get_connection()->insert_on_duplicate($this->resource->get_table_name('catalog_product_bundle_option'), $options, ['required', 'position', 'type']);
        }
    }
    /**
     * Saves given option values.
     *
     * @param array $optionValues
     *
     * @return void
     */
    public function save_option_values(array $option_values)
    {
        if (!empty($option_values)) {
            $this->resource->get_connection()->insert_on_duplicate($this->resource->get_table_name('catalog_product_bundle_option_value'), $option_values, ['title']);
        }
    }
    /**
     * Saves given selections.
     *
     * @param array $selections
     *
     * @return void
     */
    public function save_selections(array $selections)
    {
        if (!empty($selections)) {
            $this->resource->get_connection()->insert_on_duplicate($this->resource->get_table_name('catalog_product_bundle_selection'), $selections, ['selection_id', 'product_id', 'position', 'is_default', 'selection_price_type', 'selection_price_value', 'selection_qty', 'selection_can_change_qty']);
        }
    }
    /**
     * Saves bundle selection prices per website
     *
     * @param array $values
     * @return void
     */
    public function save_selection_prices(array $values): void
    {
        if (!empty($values)) {
            $this->resource->get_connection()->insert_on_duplicate($this->resource->get_table_name('catalog_product_bundle_selection_price'), $values, ['selection_price_type', 'selection_price_value']);
        }
    }
    /**
     * Saves given parent/child relations.
     *
     * @param int $parentId
     * @param array $childIds
     *
     * @return void
     */
    public function save_product_relations($parent_id, $child_ids)
    {
        $this->product_relation->process_relations($parent_id, $child_ids);
    }
}