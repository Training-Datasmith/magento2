<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store\Delete;

/**
 * Adminhtml store delete group block
 */
class Group extends \Magento\Backend\Block\Template
{
    /**
     * @inheritDoc
     */
    protected function _prepare_layout()
    {
        $item_id = $this->get_request()->get_param('group_id');
        $this->set_template('Magento_Backend::system/store/delete_group.phtml');
        $this->set_action($this->get_url('adminhtml/*/deleteGroupPost', ['group_id' => $item_id]));
        $this->add_child('confirm_deletion_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Delete Store'), 'onclick' => 'deleteForm.submit()', 'class' => 'cancel']);
        $on_click = "setLocation('" . $this->get_url('adminhtml/*/editGroup', ['group_id' => $item_id]) . "')";
        $this->add_child('cancel_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Cancel'), 'onclick' => $on_click, 'class' => 'cancel']);
        $this->add_child('back_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Back'), 'onclick' => $on_click, 'class' => 'cancel']);
        return parent::_prepare_layout();
    }
}