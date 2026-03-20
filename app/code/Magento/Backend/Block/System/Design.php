<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System;

/**
 * @api
 * @since 100.0.2
 */
class Design extends \Magento\Backend\Block\Template
{
    /**
     * {@inheritdoc}
     */
    protected function _prepare_layout()
    {
        $this->set_template('Magento_Backend::system/design/index.phtml');
        $this->get_toolbar()->add_child('add_new_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Add Design Change'), 'onclick' => "setLocation('" . $this->get_url('adminhtml/*/new') . "')", 'class' => 'add primary add-design-change']);
        $this->get_layout()->get_block('page.title')->set_page_title('Store Design Schedule');
        return parent::_prepare_layout();
    }
}