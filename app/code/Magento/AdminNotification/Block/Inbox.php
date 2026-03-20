<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Block;

/**
 * @api
 * @since 100.0.2
 */
class Inbox extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_block_group = 'Magento_AdminNotification';
        $this->_header_text = __('Messages Inbox');
        parent::_construct();
        $this->button_list->remove('add');
    }
}