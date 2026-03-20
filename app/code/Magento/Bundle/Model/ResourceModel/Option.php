<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Resource_Model;

use Magento\Bundle\Model\Option\Validator;
use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Framework\Data_Object;
use Magento\Framework\Entity_Manager\Entity_Manager;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Model\Abstract_Model;
use Magento\Framework\Model\Resource_Model\Db\Abstract_Db;
use Magento\Framework\Model\Resource_Model\Db\Context;
/**
 * Bundle Option Resource Model
 */
class Option extends Abstract_Db
{
    /**
     * @var Validator
     */
    private $validator;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var EntityManager
     */
    private $entity_manager;
    /**
     * @param Context $context
     * @param Validator $validator
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param string $connectionName
     */
    public function __construct(Context $context, Validator $validator, Metadata_Pool $metadata_pool, Entity_Manager $entity_manager, $connection_name = null)
    {
        parent::__construct($context, $connection_name);
        $this->validator = $validator;
        $this->metadata_pool = $metadata_pool;
        $this->entity_manager = $entity_manager;
    }
    /**
     * Initialize connection and define resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_bundle_option', 'option_id');
    }
    /**
     * Remove selections by option id
     *
     * @param int $optionId
     *
     * @return int
     */
    public function remove_option_selections($option_id)
    {
        return $this->get_connection()->delete($this->get_table('catalog_product_bundle_selection'), ['option_id =?' => $option_id]);
    }
    /**
     * After save process
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _after_save(Abstract_Model $object)
    {
        parent::_after_save($object);
        $connection = $this->get_connection();
        $data = new Data_Object();
        $data->set_option_id($object->get_id())->set_store_id($object->get_store_id())->set_parent_product_id($object->get_parent_id())->set_title($object->get_title());
        $connection->insert_on_duplicate($this->get_table('catalog_product_bundle_option_value'), $data->get_data(), ['title']);
        /**
         * also saving default fallback value
         */
        if (0 !== (int) $object->get_store_id()) {
            $data->set_store_id(0)->set_title($object->get_default_title());
            $connection->insert_on_duplicate($this->get_table('catalog_product_bundle_option_value'), $data->get_data(), ['title']);
        }
        return $this;
    }
    /**
     * After delete process
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _after_delete(Abstract_Model $object)
    {
        parent::_after_delete($object);
        $this->get_connection()->delete($this->get_table('catalog_product_bundle_option_value'), ['option_id = ?' => $object->get_id(), 'parent_product_id = ?' => $object->get_parent_id()]);
        return $this;
    }
    /**
     * Retrieve options searchable data
     *
     * @param int $productId
     * @param int $storeId
     *
     * @return array
     */
    public function get_searchable_data($product_id, $store_id)
    {
        $connection = $this->get_connection();
        $title = $connection->get_check_sql('option_title_store.title IS NOT NULL', 'option_title_store.title', 'option_title_default.title');
        $bind = ['store_id' => $store_id, 'product_id' => $product_id];
        $link_field = $this->metadata_pool->get_metadata(Product_Interface::class)->get_link_field();
        $select = $connection->select()->from(['opt' => $this->get_main_table()], [])->join(['option_title_default' => $this->get_table('catalog_product_bundle_option_value')], 'option_title_default.option_id = opt.option_id AND option_title_default.store_id = 0', [])->join_left(['option_title_store' => $this->get_table('catalog_product_bundle_option_value')], 'option_title_store.option_id = opt.option_id AND option_title_store.store_id = :store_id', ['title' => $title])->join(['e' => $this->get_table('catalog_product_entity')], "e.{$link_field} = opt.parent_id", [])->where('e.entity_id=:product_id');
        if (!$search_data = $connection->fetch_col($select, $bind)) {
            $search_data = [];
        }
        return $search_data;
    }
    /**
     * @inheritDoc
     */
    public function get_validation_rules_before_save()
    {
        return $this->validator;
    }
    /**
     * @inheritDoc
     */
    public function save(Abstract_Model $object)
    {
        $this->entity_manager->save($object);
        return $this;
    }
}