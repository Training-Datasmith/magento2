<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Dashboard\Orders;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Module\Manager;
use Magento\Reports\Model\Resource_Model\Order\Collection_Factory;
/**
 * Adminhtml dashboard recent orders grid
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Grid extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var CollectionFactory
     */
    protected $_collection_factory;
    /**
     * @var Manager
     */
    protected $_module_manager;
    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Manager $moduleManager
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(Context $context, Data $backend_helper, Manager $module_manager, Collection_Factory $collection_factory, array $data = [])
    {
        $this->_module_manager = $module_manager;
        $this->_collection_factory = $collection_factory;
        parent::__construct($context, $backend_helper, $data);
    }
    /**
     * Construct.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_id('lastOrdersGrid');
    }
    /**
     * Prepare collection.
     *
     * @return $this
     */
    protected function _prepare_collection()
    {
        if (!$this->_module_manager->is_enabled('Magento_Reports')) {
            return $this;
        }
        $collection = $this->_collection_factory->create()->add_item_count_expr()->join_customer_name('customer')->order_by_created_at();
        if ($this->get_param('store') || $this->get_param('website') || $this->get_param('group')) {
            if ($this->get_param('store')) {
                $collection->add_attribute_to_filter('store_id', $this->get_param('store'));
            } elseif ($this->get_param('website')) {
                $store_ids = $this->_store_manager->get_website($this->get_param('website'))->get_store_ids();
                $collection->add_attribute_to_filter('store_id', ['in' => $store_ids]);
            } elseif ($this->get_param('group')) {
                $store_ids = $this->_store_manager->get_group($this->get_param('group'))->get_store_ids();
                $collection->add_attribute_to_filter('store_id', ['in' => $store_ids]);
            }
            $collection->add_revenue_to_select();
        } else {
            $collection->add_revenue_to_select(true);
        }
        $this->set_collection($collection);
        return parent::_prepare_collection();
    }
    /**
     * Process collection after loading
     *
     * @return $this
     */
    protected function _after_load_collection()
    {
        foreach ($this->get_collection() as $item) {
            $item->get_customer() ?: $item->set_customer($item->get_billing_address()->get_name());
        }
        return $this;
    }
    /**
     * Prepares page sizes for dashboard grid with las 5 orders
     *
     * @return void
     */
    protected function _prepare_page()
    {
        $this->get_collection()->set_page_size($this->get_param($this->get_var_name_limit(), $this->_default_limit));
        // Remove count of total orders
        // $this->getCollection()->setCurPage($this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }
    /**
     * Prepare columns.
     *
     * @return $this
     */
    protected function _prepare_columns()
    {
        $this->add_column('customer', ['header' => __('Customer'), 'sortable' => false, 'index' => 'customer', 'default' => __('Guest')]);
        $this->add_column('items', ['header' => __('Items'), 'type' => 'number', 'sortable' => false, 'index' => 'items_count']);
        $base_currency_code = $this->_store_manager->get_store((int) $this->get_param('store'))->get_base_currency_code();
        $this->add_column('total', ['header' => __('Total'), 'sortable' => false, 'type' => 'currency', 'currency_code' => $this->escape_html($base_currency_code), 'index' => 'revenue']);
        $this->set_filter_visibility(false);
        $this->set_pager_visibility(false);
        return parent::_prepare_columns();
    }
    /**
     * @inheritdoc
     */
    public function get_row_url($row)
    {
        return $this->get_url('sales/order/view', ['order_id' => $row->get_id()]);
    }
}