<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\Dashboard;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Module\Manager;
use Magento\Reports\Model\Resource_Model\Order\Collection;
use Magento\Reports\Model\Resource_Model\Order\Collection_Factory;
use Magento\Store\Model\Store;
/**
 * Adminhtml dashboard totals bar
 * @api
 * @since 100.0.2
 */
class Totals extends Bar
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::dashboard/totalbar.phtml';
    /**
     * @var Manager
     */
    protected $_module_manager;
    /**
     * @var Period
     */
    private $period;
    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Manager $moduleManager
     * @param array $data
     * @param Period|null $period
     */
    public function __construct(Context $context, Collection_Factory $collection_factory, Manager $module_manager, array $data = [], ?Period $period = null)
    {
        $this->_module_manager = $module_manager;
        $this->period = $period ?? Object_Manager::get_instance()->get(Period::class);
        parent::__construct($context, $collection_factory, $data);
    }
    /**
     * @inheritDoc
     * @return $this|void
     */
    protected function _prepare_layout()
    {
        if (!$this->_module_manager->is_enabled('Magento_Reports')) {
            return $this;
        }
        $is_filter = $this->get_request()->get_param('store') || $this->get_request()->get_param('website') || $this->get_request()->get_param('group');
        $first_period = array_key_first($this->period->get_date_periods());
        $period = $this->get_request()->get_param('period', $first_period);
        /* @var $collection Collection */
        $collection = $this->_collection_factory->create()->add_create_at_period_filter($period)->calculate_totals($is_filter);
        if ($this->get_request()->get_param('store')) {
            $collection->add_field_to_filter('store_id', $this->get_request()->get_param('store'));
        } else if ($this->get_request()->get_param('website')) {
            $store_ids = $this->_store_manager->get_website($this->get_request()->get_param('website'))->get_store_ids();
            $collection->add_field_to_filter('store_id', ['in' => $store_ids]);
        } else if ($this->get_request()->get_param('group')) {
            $store_ids = $this->_store_manager->get_group($this->get_request()->get_param('group'))->get_store_ids();
            $collection->add_field_to_filter('store_id', ['in' => $store_ids]);
        } elseif (!$collection->is_live()) {
            $collection->add_field_to_filter('store_id', ['eq' => $this->_store_manager->get_store(Store::ADMIN_CODE)->get_id()]);
        }
        $collection->load();
        $totals = $collection->get_first_item();
        $this->add_total(__('Revenue'), $totals->get_revenue());
        $this->add_total(__('Tax'), $totals->get_tax());
        $this->add_total(__('Shipping'), $totals->get_shipping());
        $this->add_total(__('Quantity'), $totals->get_quantity() * 1, true);
        return $this;
    }
}