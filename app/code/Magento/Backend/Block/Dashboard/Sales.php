<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\Dashboard;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Module\Manager;
use Magento\Reports\Model\Resource_Model\Order\Collection_Factory;
/**
 * Adminhtml dashboard sales statistics bar
 *
 * @api
 * @since 100.0.2
 */
class Sales extends Bar
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::dashboard/salebar.phtml';
    /**
     * @var Manager
     */
    protected $_module_manager;
    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Manager $moduleManager
     * @param array $data
     */
    public function __construct(Context $context, Collection_Factory $collection_factory, Manager $module_manager, array $data = [])
    {
        $this->_module_manager = $module_manager;
        parent::__construct($context, $collection_factory, $data);
    }
    /**
     * Prepare layout.
     *
     * @return $this|void
     */
    protected function _prepare_layout()
    {
        if (!$this->_module_manager->is_enabled('Magento_Reports')) {
            return $this;
        }
        $is_filter = $this->get_request()->get_param('store') || $this->get_request()->get_param('website') || $this->get_request()->get_param('group');
        $collection = $this->_collection_factory->create()->calculate_sales($is_filter);
        if ($this->get_request()->get_param('store')) {
            $collection->add_field_to_filter('store_id', $this->get_request()->get_param('store'));
        } elseif ($this->get_request()->get_param('website')) {
            $store_ids = $this->_store_manager->get_website($this->get_request()->get_param('website'))->get_store_ids();
            $collection->add_field_to_filter('store_id', ['in' => $store_ids]);
        } elseif ($this->get_request()->get_param('group')) {
            $store_ids = $this->_store_manager->get_group($this->get_request()->get_param('group'))->get_store_ids();
            $collection->add_field_to_filter('store_id', ['in' => $store_ids]);
        }
        $collection->load();
        $sales = $collection->get_first_item();
        $this->add_total(__('Lifetime Sales'), $sales->get_lifetime());
        $this->add_total(__('Average Order'), $sales->get_average());
    }
}