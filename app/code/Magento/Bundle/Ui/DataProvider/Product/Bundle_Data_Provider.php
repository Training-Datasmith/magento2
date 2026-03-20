<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Ui\Data_Provider\Product;

use Magento\Bundle\Helper\Data;
use Magento\Catalog\Model\Resource_Model\Product\Collection_Factory;
use Magento\Catalog\Ui\Data_Provider\Product\Product_Data_Provider;
use Magento\Framework\App\Object_Manager;
use Magento\Ui\Data_Provider\Modifier\Modifier_Interface;
use Magento\Ui\Data_Provider\Modifier\Pool_Interface;
class Bundle_Data_Provider extends Product_Data_Provider
{
    /**
     * @var Data
     */
    protected $data_helper;
    /**
     * @var PoolInterface
     */
    private $modifiers_pool;
    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Data $dataHelper
     * @param array $meta
     * @param array $data
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param PoolInterface|null $modifiersPool
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct($name, $primary_field_name, $request_field_name, Collection_Factory $collection_factory, Data $data_helper, array $meta = [], array $data = [], array $add_field_strategies = [], array $add_filter_strategies = [], ?Pool_Interface $modifiers_pool = null)
    {
        parent::__construct($name, $primary_field_name, $request_field_name, $collection_factory, $add_field_strategies, $add_filter_strategies, $meta, $data);
        $this->data_helper = $data_helper;
        $this->modifiers_pool = $modifiers_pool ?: Object_Manager::get_instance()->get(Pool_Interface::class);
    }
    /**
     * Get data
     *
     * @return array
     */
    public function get_data()
    {
        if (!$this->get_collection()->is_loaded()) {
            $this->get_collection()->add_attribute_to_filter('type_id', $this->data_helper->get_allowed_selection_types());
            $this->get_collection()->add_filter_by_required_options();
            $this->get_collection()->add_store_filter(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $this->get_collection()->load();
        }
        $items = $this->get_collection()->to_array();
        $data = ['totalRecords' => $this->get_collection()->get_size(), 'items' => array_values($items)];
        /** @var ModifierInterface $modifier */
        foreach ($this->modifiers_pool->get_modifiers_instances() as $modifier) {
            $data = $modifier->modify_data($data);
        }
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function get_meta()
    {
        $meta = parent::get_meta();
        /** @var ModifierInterface $modifier */
        foreach ($this->modifiers_pool->get_modifiers_instances() as $modifier) {
            $meta = $modifier->modify_meta($meta);
        }
        return $meta;
    }
}