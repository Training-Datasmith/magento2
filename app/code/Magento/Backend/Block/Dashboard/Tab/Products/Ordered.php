<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Dashboard\Tab\Products;

/**
 * Adminhtml dashboard most ordered products grid
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Ordered extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory
     */
    protected $_collection_factory;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_module_manager;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backend_helper, \Magento\Framework\Module\Manager $module_manager, \Magento\Sales\Model\Resource_Model\Report\Bestsellers\Collection_Factory $collection_factory, array $data = [])
    {
        $this->_collection_factory = $collection_factory;
        $this->_module_manager = $module_manager;
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
        $this->set_id('productsOrderedGrid');
    }
    /**
     * @inheritdoc
     */
    protected function _prepare_collection()
    {
        if (!$this->_module_manager->is_enabled('Magento_Sales')) {
            return $this;
        }
        if ($this->get_param('website')) {
            $store_ids = $this->_store_manager->get_website($this->get_param('website'))->get_store_ids();
            $store_id = array_pop($store_ids);
        } elseif ($this->get_param('group')) {
            $store_ids = $this->_store_manager->get_group($this->get_param('group'))->get_store_ids();
            $store_id = array_pop($store_ids);
        } else {
            $store_id = (int) $this->get_param('store');
        }
        $collection = $this->_collection_factory->create()->set_model(\Magento\Catalog\Model\Product::class)->add_store_filter($store_id);
        $this->set_collection($collection);
        return parent::_prepare_collection();
    }
    /**
     * @inheritdoc
     */
    protected function _prepare_columns()
    {
        $this->add_column('name', ['header' => __('Product'), 'sortable' => false, 'index' => 'product_name', 'header_css_class' => 'col-product', 'column_css_class' => 'col-product']);
        $this->add_column('price', ['header' => __('Price'), 'type' => 'currency', 'currency_code' => (string) $this->_store_manager->get_store((int) $this->get_param('store'))->get_base_currency_code(), 'sortable' => false, 'index' => 'product_price']);
        $this->add_column('ordered_qty', ['header' => __('Quantity'), 'sortable' => false, 'index' => 'qty_ordered', 'type' => 'number', 'header_css_class' => 'col-qty', 'column_css_class' => 'col-qty']);
        $this->set_filter_visibility(false);
        $this->set_pager_visibility(false);
        return parent::_prepare_columns();
    }
    /**
     * Returns row url to show in admin dashboard
     * $row is bestseller row wrapped in Product model
     *
     * @param \Magento\Catalog\Model\Product $row
     * @return string
     */
    public function get_row_url($row)
    {
        // getId() would return id of bestseller row, and product id we get by getProductId()
        $product_id = $row->get_product_id();
        // No url is possible for non-existing products
        if (!$product_id) {
            return '';
        }
        $params = ['id' => $product_id];
        if ($this->get_request()->get_param('store')) {
            $params['store'] = $this->get_request()->get_param('store');
        }
        return $this->get_url('catalog/product/edit', $params);
    }
}