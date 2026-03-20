<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Resource_Model;

use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Entity_Manager\Entity_Manager;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Model\Resource_Model\Db\Context;
/**
 * Bundle Selection Resource Model
 *
 * @api
 * @since 100.0.2
 */
class Selection extends \Magento\Framework\Model\Resource_Model\Db\Abstract_Db
{
    /**
     * @var MetadataPool
     * @since 100.1.0
     */
    protected $metadata_pool;
    /**
     * @var EntityManager
     */
    private $entity_manager;
    /**
     * Selection constructor.
     *
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param null|string $connectionName
     * @param EntityManager|null $entityManager
     */
    public function __construct(Context $context, Metadata_Pool $metadata_pool, $connection_name = null, ?Entity_Manager $entity_manager = null)
    {
        parent::__construct($context, $connection_name);
        $this->metadata_pool = $metadata_pool;
        $this->entity_manager = $entity_manager ?: Object_Manager::get_instance()->get(Entity_Manager::class);
    }
    /**
     * Define main table and id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_bundle_selection', 'selection_id');
    }
    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     */
    public function get_children_ids($parent_id, $required = true)
    {
        $children_ids = [];
        $not_required = [];
        $connection = $this->get_connection();
        $link_field = $this->metadata_pool->get_metadata(Product_Interface::class)->get_link_field();
        $select = $connection->select()->from(['tbl_selection' => $this->get_main_table()], ['product_id', 'parent_product_id', 'option_id'])->join(['e' => $this->get_table('catalog_product_entity')], 'e.entity_id = tbl_selection.product_id AND e.required_options=0', [])->join(['parent' => $this->get_table('catalog_product_entity')], 'tbl_selection.parent_product_id = parent.' . $link_field)->join(['tbl_option' => $this->get_table('catalog_product_bundle_option')], 'tbl_option.option_id = tbl_selection.option_id', ['required'])->where('parent.entity_id = :parent_id');
        foreach ($connection->fetch_all($select, ['parent_id' => $parent_id]) as $row) {
            if ($row['required']) {
                $children_ids[$row['option_id']][$row['product_id']] = $row['product_id'];
            } else {
                $not_required[$row['option_id']][$row['product_id']] = $row['product_id'];
            }
        }
        if (!$required) {
            $children_ids = array_merge($children_ids, $not_required);
        } else {
            if (!$children_ids) {
                foreach ($not_required as $grouped_children_ids) {
                    foreach ($grouped_children_ids as $child_id) {
                        $children_ids[0][$child_id] = $child_id;
                    }
                }
            }
            if (!$children_ids) {
                $children_ids = [[]];
            }
        }
        return $children_ids;
    }
    /**
     * Retrieve array of related bundle product ids by selection product id(s)
     *
     * @param int|array $childId
     * @return array
     */
    public function get_parent_ids_by_child($child_id)
    {
        $connection = $this->get_connection();
        $metadata = $this->metadata_pool->get_metadata(Product_Interface::class);
        $select = $connection->select()->distinct(true)->from($this->get_main_table(), '')->join(['e' => $this->metadata_pool->get_metadata(Product_Interface::class)->get_entity_table()], 'e.' . $metadata->get_link_field() . ' = ' . $this->get_main_table() . '.parent_product_id', ['e.entity_id as parent_product_id'])->where($this->get_main_table() . '.product_id IN(?)', $child_id, \Zend_Db::INT_TYPE);
        return $connection->fetch_col($select);
    }
    /**
     * Save bundle item price per website
     *
     * @param \Magento\Bundle\Model\Selection $item
     * @return void
     */
    public function save_selection_price($item)
    {
        $connection = $this->get_connection();
        if ($item->get_default_price_scope()) {
            $connection->delete($this->get_table('catalog_product_bundle_selection_price'), ['selection_id = ?' => $item->get_selection_id(), 'website_id = ?' => $item->get_website_id(), 'parent_product_id = ?' => $item->get_parent_product_id()]);
        } else {
            $values = ['selection_id' => $item->get_selection_id(), 'website_id' => $item->get_website_id(), 'selection_price_type' => $item->get_selection_price_type() ?? 0, 'selection_price_value' => $item->get_selection_price_value() ?? 0, 'parent_product_id' => $item->get_parent_product_id()];
            $connection->insert_on_duplicate($this->get_table('catalog_product_bundle_selection_price'), $values, ['selection_price_type', 'selection_price_value']);
        }
    }
    /**
     * @inheritdoc
     *
     * @since 100.2.0
     */
    public function save(\Magento\Framework\Model\Abstract_Model $object)
    {
        $this->entity_manager->save($object);
        return $this;
    }
}