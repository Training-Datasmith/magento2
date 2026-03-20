<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Dashboard;

use Magento\Framework\Model\Resource_Model\Db\Collection\Abstract_Collection;
/**
 * Adminhtml dashboard tab abstract
 */
abstract class Abstract_Dashboard extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Backend\Helper\Dashboard\AbstractDashboard
     */
    protected $_data_helper = null;
    /**
     * @var \Magento\Reports\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_collection_factory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Reports\Model\Resource_Model\Order\Collection_Factory $collection_factory, array $data = [])
    {
        $this->_collection_factory = $collection_factory;
        parent::__construct($context, $data);
    }
    /**
     * Return a collection
     *
     * @return array|AbstractCollection|\Magento\Eav\Model\Entity\Collection\Abstract
     */
    public function get_collection()
    {
        return $this->get_data_helper()->get_collection();
    }
    /**
     * Return items count
     *
     * @return int
     */
    public function get_count()
    {
        return $this->get_data_helper()->get_count();
    }
    /**
     * Get data helper
     *
     * @return \Magento\Backend\Helper\Dashboard\AbstractDashboard
     */
    public function get_data_helper()
    {
        return $this->_data_helper;
    }
    /**
     * Prepare any data for display, if required
     *
     * @return $this
     */
    protected function _prepare_data()
    {
        return $this;
    }
    /**
     * Ensure data is prepared before layout
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $this->_prepare_data();
        return parent::_prepare_layout();
    }
}