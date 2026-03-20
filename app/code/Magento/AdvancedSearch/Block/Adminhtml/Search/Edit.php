<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Block\Adminhtml\Search;

/**
 * Search queries relations grid container
 *
 * @api
 * @since 100.0.2
 */
class Edit extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Enable grid container
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_block_group = 'Magento_AdvancedSearch';
        $this->_controller = 'adminhtml_search';
        $this->_header_text = __('Related Search Terms');
        $this->_add_button_label = __('Add New Search Term');
        parent::_construct();
        $this->button_list->remove('add');
    }
}