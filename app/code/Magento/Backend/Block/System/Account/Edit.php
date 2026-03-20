<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Account;

/**
 * Adminhtml edit admin user account
 *
 * @api
 * @since 100.0.2
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialise the page
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_block_group = 'Magento_Backend';
        $this->_controller = 'system_account';
        $this->button_list->update('save', 'label', __('Save Account'));
        $this->button_list->remove('delete');
        $this->button_list->remove('back');
    }
    /**
     * Return a Phrase for the header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function get_header_text()
    {
        return __('My Account');
    }
}