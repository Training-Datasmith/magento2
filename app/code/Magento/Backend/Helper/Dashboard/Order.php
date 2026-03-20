<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Helper\Dashboard;

use Magento\Framework\App\Object_Manager;
/**
 * Adminhtml dashboard helper for orders
 *
 * @api
 * @since 100.0.2
 */
class Order extends Abstract_Dashboard
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Order\Collection
     */
    protected $_order_collection;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 100.0.6
     */
    protected $_store_manager;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Order\Collection $orderCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Reports\Model\Resource_Model\Order\Collection $order_collection, ?\Magento\Store\Model\Store_Manager_Interface $store_manager = null)
    {
        $this->_order_collection = $order_collection;
        $this->_store_manager = $store_manager ?: Object_Manager::get_instance()->get(\Magento\Store\Model\Store_Manager_Interface::class);
        parent::__construct($context);
    }
    /**
     * Initialize Collection
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _init_collection()
    {
        $is_filter = $this->get_param('store') || $this->get_param('website') || $this->get_param('group');
        $this->_collection = $this->_order_collection->prepare_summary($this->get_param('period'), 0, 0, $is_filter);
        if ($this->get_param('store')) {
            $this->_collection->add_field_to_filter('store_id', $this->get_param('store'));
        } elseif ($this->get_param('website')) {
            $store_ids = $this->_store_manager->get_website($this->get_param('website'))->get_store_ids();
            $this->_collection->add_field_to_filter('store_id', ['in' => implode(',', $store_ids)]);
        } elseif ($this->get_param('group')) {
            $store_ids = $this->_store_manager->get_group($this->get_param('group'))->get_store_ids();
            $this->_collection->add_field_to_filter('store_id', ['in' => implode(',', $store_ids)]);
        } elseif (!$this->_collection->is_live()) {
            $this->_collection->add_field_to_filter('store_id', ['eq' => $this->_store_manager->get_store(\Magento\Store\Model\Store::ADMIN_CODE)->get_id()]);
        }
        $this->_collection->load();
    }
}