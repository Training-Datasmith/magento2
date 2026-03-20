<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search;

/**
 * Bundle selection product grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Bundle\Helper\Data
     */
    protected $_bundle_data = null;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product_factory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Bundle\Helper\Data $bundleData
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backend_helper, \Magento\Catalog\Model\Product_Factory $product_factory, \Magento\Bundle\Helper\Data $bundle_data, array $data = [])
    {
        $this->_bundle_data = $bundle_data;
        $this->_product_factory = $product_factory;
        parent::__construct($context, $backend_helper, $data);
    }
    /**
     * Initialization
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_id('bundle_selection_search_grid');
        $this->set_row_click_callback('bSelection.productGridRowClick.bind(bSelection)');
        $this->set_checkbox_check_callback('bSelection.productGridCheckboxCheck.bind(bSelection)');
        $this->set_row_init_callback('bSelection.productGridRowInit.bind(bSelection)');
        $this->set_default_sort('id');
        $this->set_use_ajax(true);
    }
    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
    protected function _prepare_filter_buttons()
    {
        $this->get_child_block('reset_filter_button')->set_data('onclick', $this->get_js_object_name() . '.resetFilter(bSelection.gridUpdateCallback)');
        $this->get_child_block('search_button')->set_data('onclick', $this->get_js_object_name() . '.doFilter(bSelection.gridUpdateCallback)');
    }
    /**
     * Initialize grid before rendering
     *
     * @return $this
     */
    protected function _before_to_html()
    {
        $this->set_id($this->get_id() . '_' . $this->get_index());
        return parent::_before_to_html();
    }
    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     */
    protected function _prepare_collection()
    {
        $collection = $this->_product_factory->create()->get_collection()->set_order('id')->add_attribute_to_select('name')->add_attribute_to_select('sku')->add_attribute_to_select('price')->add_attribute_to_select('attribute_set_id')->add_attribute_to_filter('entity_id', ['nin' => $this->_get_selected_products()])->add_attribute_to_filter('type_id', ['in' => $this->get_allowed_selection_types()])->add_filter_by_required_options()->add_store_filter(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        if ($this->get_first_show()) {
            $collection->add_id_filter('-1');
            $this->set_empty_text(__('What are you looking for?'));
        }
        $this->set_collection($collection);
        return parent::_prepare_collection();
    }
    /**
     * Initialize grid columns
     *
     * @return $this
     */
    protected function _prepare_columns()
    {
        $this->add_column('id', ['header' => __('ID'), 'index' => 'entity_id', 'renderer' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox::class, 'type' => 'skip-list']);
        $this->add_column('name', ['header' => __('Product'), 'index' => 'name', 'header_css_class' => 'col-name', 'column_css_class' => 'name col-name']);
        $this->add_column('sku', ['header' => __('SKU'), 'width' => '80px', 'index' => 'sku', 'header_css_class' => 'col-sku', 'column_css_class' => 'sku col-sku']);
        $this->add_column('price', ['header' => __('Price'), 'align' => 'center', 'type' => 'currency', 'index' => 'price', 'header_css_class' => 'col-price', 'column_css_class' => 'col-price']);
        return parent::_prepare_columns();
    }
    /**
     * Retrieve grid reload url
     *
     * @return string
     */
    public function get_grid_url()
    {
        return $this->get_url('adminhtml/bundle_selection/grid', ['index' => $this->get_index(), 'productss' => implode(',', $this->_get_products())]);
    }
    /**
     * Get selected products
     *
     * @return mixed
     */
    protected function _get_selected_products()
    {
        $products = $this->get_request()->get_post('selected_products', explode(',', $this->get_request()->get_param('productss', '')));
        return $products;
    }
    /**
     * Get products
     *
     * @return array
     */
    protected function _get_products()
    {
        if ($products = $this->get_request()->get_post('products', null)) {
            return $products;
        } else if ($productss = $this->get_request()->get_param('productss', null)) {
            return explode(',', $productss);
        } else {
            return [];
        }
    }
    /**
     * Retrieve array of allowed product types for bundle selection product
     *
     * @return array
     */
    public function get_allowed_selection_types()
    {
        return $this->_bundle_data->get_allowed_selection_types();
    }
}