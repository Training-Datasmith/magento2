<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Search;

/**
 * Search Order Model
 *
 * @api
 * @since 100.0.2
 */
class Order extends \Magento\Framework\Data_Object
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtml_data = null;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_collection_factory;
    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     */
    public function __construct(\Magento\Sales\Model\Resource_Model\Order\Collection_Factory $collection_factory, \Magento\Backend\Helper\Data $adminhtml_data)
    {
        $this->_collection_factory = $collection_factory;
        $this->_adminhtml_data = $adminhtml_data;
    }
    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        if (!$this->has_start() || !$this->has_limit() || !$this->has_query()) {
            $this->set_results($result);
            return $this;
        }
        $query = $this->get_query();
        //TODO: add full name logic
        $collection = $this->_collection_factory->create()->add_attribute_to_select('*')->add_attribute_to_search_filter([['attribute' => 'increment_id', 'like' => $query . '%'], ['attribute' => 'billing_firstname', 'like' => $query . '%'], ['attribute' => 'billing_lastname', 'like' => $query . '%'], ['attribute' => 'billing_telephone', 'like' => $query . '%'], ['attribute' => 'billing_postcode', 'like' => $query . '%'], ['attribute' => 'shipping_firstname', 'like' => $query . '%'], ['attribute' => 'shipping_lastname', 'like' => $query . '%'], ['attribute' => 'shipping_telephone', 'like' => $query . '%'], ['attribute' => 'shipping_postcode', 'like' => $query . '%']])->set_cur_page($this->get_start())->set_page_size($this->get_limit())->load();
        foreach ($collection as $order) {
            $result[] = ['id' => 'order/1/' . $order->get_id(), 'type' => __('Order'), 'name' => __('Order #%1', $order->get_increment_id()), 'description' => $order->get_firstname() . ' ' . $order->get_lastname(), 'url' => $this->_adminhtml_data->get_url('sales/order/view', ['order_id' => $order->get_id()])];
        }
        $this->set_results($result);
        return $this;
    }
}