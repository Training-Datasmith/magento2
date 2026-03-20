<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Dashboard;

/**
 * Adminhtml dashboard diagram tabs
 * @deprecated dashboard graphs were migrated to dynamic chart.js solution
 * @see dashboard.diagrams in adminhtml_dashboard_index.xml
 */
class Diagrams extends \Magento\Backend\Block\Widget\Tabs
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
        $this->set_id('diagram_tab');
        $this->set_dest_element_id('diagram_tab_content');
    }
    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $this->add_tab('orders', ['label' => __('Orders'), 'content' => $this->get_layout()->create_block(\Magento\Backend\Block\Dashboard\Tab\Orders::class)->to_html(), 'active' => true]);
        $this->add_tab('amounts', ['label' => __('Amounts'), 'content' => $this->get_layout()->create_block(\Magento\Backend\Block\Dashboard\Tab\Amounts::class)->to_html()]);
        return parent::_prepare_layout();
    }
}