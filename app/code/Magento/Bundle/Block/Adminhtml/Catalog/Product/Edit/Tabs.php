<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit;

/**
 * Adminhtml product edit tabs
 */
class Tabs extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs
{
    /**
     * @var string
     */
    protected $_attribute_tab_block = \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes::class;
    /**
     * Prepare the layout
     *
     * @return void
     */
    protected function _prepare_layout()
    {
        parent::_prepare_layout();
        $this->add_tab('bundle_items', ['label' => __('Bundle Items'), 'url' => $this->get_url('adminhtml/*/bundles', ['_current' => true]), 'class' => 'ajax']);
        $this->bind_shadow_tabs('bundle_items', 'customer_options');
    }
}