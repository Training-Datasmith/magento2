<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

/**
 * Class AbstractSearchResult
 */
abstract class Abstract_Search_Result extends Abstract_Data_Object implements Search_Result_Interface
{
    /**
     * Data Interface name
     *
     * @var string
     */
    protected $data_interface = \Magento\Framework\Data_Object::class;
    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $event_prefix = '';
    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $event_object = '';
    /**
     * Event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $event_manager = null;
    /**
     * Total items number
     *
     * @var int
     */
    protected $total_records;
    /**
     * Loading state flag
     *
     * @var bool
     */
    protected $is_loaded;
    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface
     */
    protected $entity_factory;
    /**
     * @var \Magento\Framework\DB\QueryInterface
     */
    protected $query;
    /**
     * @var \Magento\Framework\DB\Select
     * @deprecated 101.0.0
     */
    protected $select;
    /**
     * @var \Magento\Framework\Data\SearchResultIteratorFactory
     */
    protected $result_iterator_factory;
    /**
     * @param \Magento\Framework\DB\QueryInterface $query
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Data\SearchResultIteratorFactory $resultIteratorFactory
     */
    public function __construct(\Magento\Framework\DB\Query_Interface $query, \Magento\Framework\Data\Collection\Entity_Factory_Interface $entity_factory, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Framework\Data\Search_Result_Iterator_Factory $result_iterator_factory)
    {
        $this->query = $query;
        $this->event_manager = $event_manager;
        $this->entity_factory = $entity_factory;
        $this->result_iterator_factory = $result_iterator_factory;
        $this->init();
    }
    /**
     * Standard query builder initialization
     *
     * @return void
     */
    abstract protected function init();
    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function get_items()
    {
        $this->load();
        return $this->data['items'];
    }
    /**
     * @param \Magento\Framework\DataObject[] $items
     * @return $this
     */
    public function set_items(?array $items = null)
    {
        $this->data['items'] = $items;
        return $this;
    }
    /**
     * @return int
     */
    public function get_total_count()
    {
        if (!isset($this->data['total_count'])) {
            $this->data['total_count'] = $this->query->get_size();
        }
        return $this->data['total_count'];
    }
    /**
     * @param int $totalCount
     * @return $this
     */
    public function set_total_count($total_count)
    {
        $this->data['total_count'] = $total_count;
        return $this;
    }
    /**
     * @return \Magento\Framework\Api\CriteriaInterface
     */
    public function get_search_criteria()
    {
        if (!isset($this->data['search_criteria'])) {
            $this->data['search_criteria'] = $this->query->get_criteria();
        }
        return $this->data['search_criteria'];
    }
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function set_search_criteria(?\Magento\Framework\Api\Search_Criteria_Interface $search_criteria = null)
    {
        return $this;
    }
    /**
     * @return \Magento\Framework\Data\SearchResultIterator
     */
    public function create_iterator()
    {
        return $this->result_iterator_factory->create(['searchResult' => $this, 'query' => $this->query]);
    }
    /**
     * @param array $arguments
     * @return \Magento\Framework\DataObject|mixed
     */
    public function create_data_object(array $arguments = [])
    {
        return $this->entity_factory->create($this->get_data_interface_name(), $arguments);
    }
    /**
     * @return string
     */
    public function get_id_field_name()
    {
        return $this->query->get_id_field_name();
    }
    /**
     * @return int
     */
    public function get_size()
    {
        return $this->query->get_size();
    }
    /**
     * Get collection item identifier
     *
     * @param \Magento\Framework\DataObject $item
     * @return mixed
     */
    public function get_item_id(\Magento\Framework\Data_Object $item)
    {
        $field = $this->query->get_id_field_name();
        if ($field) {
            return $item->get_data($field);
        }
        return $item->get_id();
    }
    /**
     * @return bool
     */
    protected function is_loaded()
    {
        return $this->is_loaded;
    }
    /**
     * Load data
     *
     * @return void
     */
    protected function load()
    {
        if (!$this->is_loaded()) {
            $this->before_load();
            $data = $this->query->fetch_all();
            $this->data['items'] = [];
            if (is_array($data)) {
                foreach ($data as $row) {
                    $item = $this->create_data_object(['data' => $row]);
                    $this->add_item($item);
                }
            }
            $this->set_is_loaded(true);
            $this->after_load();
        }
    }
    /**
     * Set loading status flag
     *
     * @param bool $flag
     * @return void
     */
    protected function set_is_loaded($flag = true)
    {
        $this->is_loaded = $flag;
    }
    /**
     * Adding item to item array
     *
     * @param \Magento\Framework\DataObject $item
     * @return void
     * @throws \Exception
     */
    protected function add_item(\Magento\Framework\Data_Object $item)
    {
        $item_id = $this->get_item_id($item);
        if ($item_id !== null) {
            if (isset($this->data['items'][$item_id])) {
                throw new \Exception('Item (' . get_class($item) . ') with the same ID "' . $item->get_id() . '" already exists.');
            }
            $this->data['items'][$item_id] = $item;
        } else {
            $this->data['items'][] = $item;
        }
    }
    /**
     * Dispatch "before" load method events
     *
     * @return void
     */
    protected function before_load()
    {
        $this->event_manager->dispatch('abstract_search_result_load_before', ['collection' => $this]);
        if ($this->event_prefix && $this->event_object) {
            $this->event_manager->dispatch($this->event_prefix . '_load_before', [$this->event_object => $this]);
        }
    }
    /**
     * Dispatch "after" load method events
     *
     * @return void
     */
    protected function after_load()
    {
        $this->event_manager->dispatch('abstract_search_result_load_after', ['collection' => $this]);
        if ($this->event_prefix && $this->event_object) {
            $this->event_manager->dispatch($this->event_prefix . '_load_after', [$this->event_object => $this]);
        }
    }
    /**
     * Set Data Interface name for collection items
     *
     * @param string $dataInterface
     * @return void
     */
    protected function set_data_interface_name($data_interface)
    {
        if (is_string($data_interface)) {
            $this->data_interface = $data_interface;
        }
    }
    /**
     * Get Data Interface name for collection items
     *
     * @return string
     */
    protected function get_data_interface_name()
    {
        return $this->data_interface;
    }
    /**
     * @return \Magento\Framework\DB\QueryInterface
     */
    protected function get_query()
    {
        return $this->query;
    }
}