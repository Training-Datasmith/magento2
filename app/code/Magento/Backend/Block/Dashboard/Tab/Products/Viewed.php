<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Dashboard\Tab\Products;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\Final_Price;
/**
 * Adminhtml dashboard most viewed products grid
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Viewed extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_products_factory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productsFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backend_helper, \Magento\Reports\Model\Resource_Model\Product\Collection_Factory $products_factory, array $data = [])
    {
        $this->_products_factory = $products_factory;
        parent::__construct($context, $backend_helper, $data);
    }
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_id('productsReviewedGrid');
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepare_collection()
    {
        if ($this->get_param('website')) {
            $store_ids = $this->_store_manager->get_website($this->get_param('website'))->get_store_ids();
            $store_id = array_pop($store_ids);
        } elseif ($this->get_param('group')) {
            $store_ids = $this->_store_manager->get_group($this->get_param('group'))->get_store_ids();
            $store_id = array_pop($store_ids);
        } else {
            $store_id = (int) $this->get_param('store');
        }
        $collection = $this->_products_factory->create()->add_attribute_to_select('*')->add_views_count()->set_store_id($store_id)->add_store_filter($store_id);
        $this->set_collection($collection);
        parent::_prepare_collection();
        /** @var Product $product */
        foreach ($collection as $product) {
            $product->set_price($product->get_price_info()->get_price(Final_Price::PRICE_CODE)->get_value());
        }
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepare_columns()
    {
        $this->add_column('name', ['header' => __('Product'), 'sortable' => false, 'index' => 'name']);
        $this->add_column('price', ['header' => __('Price'), 'type' => 'currency', 'currency_code' => (string) $this->_store_manager->get_store((int) $this->get_param('store'))->get_base_currency_code(), 'sortable' => false, 'index' => 'price']);
        $this->add_column('views', ['header' => __('Views'), 'sortable' => false, 'index' => 'views', 'header_css_class' => 'col-views', 'column_css_class' => 'col-views']);
        $this->set_filter_visibility(false);
        $this->set_pager_visibility(false);
        return parent::_prepare_columns();
    }
    /**
     * {@inheritdoc}
     */
    public function get_row_url($row)
    {
        $params = ['id' => $row->get_id()];
        if ($this->get_request()->get_param('store')) {
            $params['store'] = $this->get_request()->get_param('store');
        }
        return $this->get_url('catalog/product/edit', $params);
    }
}