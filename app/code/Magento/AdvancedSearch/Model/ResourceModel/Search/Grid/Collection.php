<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Resource_Model\Search\Grid;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Search\Model\Resource_Model\Query\Collection
{
    /**
     * @param mixed $connection
     * @param mixed $resource
     */
    public function __construct(\Magento\Framework\Data\Collection\Entity_Factory $entity_factory, \Psr\Log\Logger_Interface $logger, \Magento\Framework\Data\Collection\Db\Fetch_Strategy_Interface $fetch_strategy, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Store\Model\Store_Manager_Interface $store_manager, \Magento\Framework\DB\Helper $resource_helper, protected \Magento\Framework\Registry $_registry_manager, ?\Magento\Framework\DB\Adapter\Adapter_Interface $connection = null, ?\Magento\Framework\Model\Resource_Model\Db\Abstract_Db $resource = null)
    {
        parent::__construct($entity_factory, $logger, $fetch_strategy, $event_manager, $store_manager, $resource_helper, $connection, $resource);
    }
    /**
     * Initialize select
     *
     * @return $this
     */
    protected function _init_select(): static
    {
        parent::_init_select();
        $query_id = $this->get_query()->get_id();
        if ($query_id) {
            $this->add_field_to_filter('query_id', ['nin' => $query_id]);
        }
        return $this;
    }
    /**
     *  Retrieve a value from registry by a key
     *
     * @return \Magento\Search\Model\Query
     */
    public function get_query()
    {
        return $this->_registry_manager->registry('current_catalog_search');
    }
}