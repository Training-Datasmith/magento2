<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option;

/**
 * Bundle selection product block
 */
class Search extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Bundle::product/edit/bundle/option/search.phtml';
    /**
     * Initialise the block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->set_id('bundle_option_selection_search');
    }
    /**
     * Create search grid
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $this->set_child('grid', $this->get_layout()->create_block(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid::class, 'adminhtml.catalog.product.edit.tab.bundle.option.search.grid'));
        return parent::_prepare_layout();
    }
    /**
     * Prepare search grid
     *
     * @return $this
     */
    protected function _before_to_html()
    {
        $this->get_child_block('grid')->set_index($this->get_index())->set_first_show($this->get_first_show());
        return parent::_before_to_html();
    }
}