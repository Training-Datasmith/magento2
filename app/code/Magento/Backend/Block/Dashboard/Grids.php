<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\Dashboard;

use Magento\Backend\Block\Dashboard\Tab\Products\Ordered;
use Magento\Backend\Block\Widget\Tabs;
/**
 * Adminhtml dashboard bottom tabs
 *
 * @api
 * @since 100.0.2
 */
class Grids extends Tabs
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';
    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_id('grid_tab');
        $this->set_dest_element_id('grid_tab_content');
    }
    /**
     * Prepare layout for dashboard bottom tabs
     *
     * To load block statically:
     *     1) content must be generated
     *     2) url should not be specified
     *     3) class should not be 'ajax'
     * To load with ajax:
     *     1) do not load content
     *     2) specify url (BE CAREFUL)
     *     3) specify class 'ajax'
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        // load this active tab statically
        $this->add_tab('ordered_products', ['label' => __('Bestsellers'), 'content' => $this->get_layout()->create_block(Ordered::class)->to_html(), 'active' => true]);
        // load other tabs with ajax
        $this->add_tab('reviewed_products', ['label' => __('Most Viewed Products'), 'url' => $this->get_url('adminhtml/*/productsViewed', ['_current' => true]), 'class' => 'ajax']);
        $this->add_tab('new_customers', ['label' => __('New Customers'), 'url' => $this->get_url('adminhtml/*/customersNewest', ['_current' => true]), 'class' => 'ajax']);
        $this->add_tab('customers', ['label' => __('Customers'), 'url' => $this->get_url('adminhtml/*/customersMost', ['_current' => true]), 'class' => 'ajax']);
        return parent::_prepare_layout();
    }
}