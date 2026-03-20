<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Dashboard\Tab;

/**
 * Adminhtml dashboard order amounts diagram
 * @deprecated dashboard graphs were migrated to dynamic chart.js solution
 * @see dashboard.chart.amounts in adminhtml_dashboard_index.xml
 */
class Amounts extends \Magento\Backend\Block\Dashboard\Graph
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Dashboard\Data $dashboardData
     * @param \Magento\Backend\Helper\Dashboard\Order $dataHelper
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Reports\Model\Resource_Model\Order\Collection_Factory $collection_factory, \Magento\Backend\Helper\Dashboard\Data $dashboard_data, \Magento\Backend\Helper\Dashboard\Order $data_helper, array $data = [])
    {
        $this->_data_helper = $data_helper;
        parent::__construct($context, $collection_factory, $dashboard_data, $data);
    }
    /**
     * Initialize object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->set_html_id('amounts');
        parent::_construct();
    }
    /**
     * Prepare chart data
     *
     * @return void
     */
    protected function _prepare_data()
    {
        $this->get_data_helper()->set_param('store', $this->get_request()->get_param('store'));
        $this->get_data_helper()->set_param('website', $this->get_request()->get_param('website'));
        $this->get_data_helper()->set_param('group', $this->get_request()->get_param('group'));
        $this->set_data_rows('revenue');
        $this->_axis_maps = ['x' => 'range', 'y' => 'revenue'];
        parent::_prepare_data();
    }
}