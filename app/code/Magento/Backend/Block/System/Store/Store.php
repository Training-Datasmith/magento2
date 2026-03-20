<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store;

/**
 * Adminhtml store content block
 *
 * @api
 * @since 100.0.2
 */
class Store extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_block_group = 'Magento_Backend';
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_block_group = 'Magento_Backend';
        $this->_controller = 'system_store';
        $this->_header_text = __('Stores');
        parent::_construct();
        /* Update default add button to add website button */
        $this->button_list->update('add', 'label', __('Create Website'));
        $this->button_list->update('add', 'onclick', "setLocation('" . $this->get_url('adminhtml/*/newWebsite') . "')");
        /* Add Store Group button */
        $this->button_list->add('add_group', ['label' => __('Create Store'), 'onclick' => 'setLocation(\'' . $this->get_url('adminhtml/*/newGroup') . '\')', 'class' => 'add add-store'], 1);
        /* Add Store button */
        $this->button_list->add('add_store', ['label' => __('Create Store View'), 'onclick' => 'setLocation(\'' . $this->get_url('adminhtml/*/newStore') . '\')', 'class' => 'add add-store-view']);
    }
}