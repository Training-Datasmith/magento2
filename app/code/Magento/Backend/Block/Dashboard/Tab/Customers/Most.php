<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Dashboard\Tab\Customers;

/**
 * Adminhtml dashboard most active buyers
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Most extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_collection_factory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backend_helper, \Magento\Reports\Model\Resource_Model\Order\Collection_Factory $collection_factory, array $data = [])
    {
        $this->_collection_factory = $collection_factory;
        parent::__construct($context, $backend_helper, $data);
    }
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_id('customersMostGrid');
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepare_collection()
    {
        $collection = $this->_collection_factory->create();
        /* @var $collection \Magento\Reports\Model\ResourceModel\Order\Collection */
        $collection->group_by_customer()->add_orders_count()->join_customer_name();
        $store_filter = 0;
        if ($this->get_param('store')) {
            $collection->add_attribute_to_filter('store_id', $this->get_param('store'));
            $store_filter = 1;
        } elseif ($this->get_param('website')) {
            $store_ids = $this->_store_manager->get_website($this->get_param('website'))->get_store_ids();
            $collection->add_attribute_to_filter('store_id', ['in' => $store_ids]);
        } elseif ($this->get_param('group')) {
            $store_ids = $this->_store_manager->get_group($this->get_param('group'))->get_store_ids();
            $collection->add_attribute_to_filter('store_id', ['in' => $store_ids]);
        }
        $collection->add_sum_avg_totals($store_filter)->order_by_total_amount();
        $this->set_collection($collection);
        return parent::_prepare_collection();
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepare_columns()
    {
        $this->add_column('name', ['header' => __('Customer'), 'sortable' => false, 'index' => 'name']);
        $this->add_column('orders_count', ['header' => __('Orders'), 'sortable' => false, 'index' => 'orders_count', 'type' => 'number', 'header_css_class' => 'col-orders', 'column_css_class' => 'col-orders']);
        $base_currency_code = (string) $this->_store_manager->get_store((int) $this->get_param('store'))->get_base_currency_code();
        $this->add_column('orders_avg_amount', ['header' => __('Average'), 'sortable' => false, 'type' => 'currency', 'currency_code' => $base_currency_code, 'index' => 'orders_avg_amount', 'header_css_class' => 'col-avg', 'column_css_class' => 'col-avg']);
        $this->add_column('orders_sum_amount', ['header' => __('Total'), 'sortable' => false, 'type' => 'currency', 'currency_code' => $base_currency_code, 'index' => 'orders_sum_amount', 'header_css_class' => 'col-total', 'column_css_class' => 'col-total']);
        $this->set_filter_visibility(false);
        $this->set_pager_visibility(false);
        return parent::_prepare_columns();
    }
    /**
     * {@inheritdoc}
     */
    public function get_row_url($row)
    {
        return $this->get_url('customer/index/edit', ['id' => $row->get_customer_id()]);
    }
}