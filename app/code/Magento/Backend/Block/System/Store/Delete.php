<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store;

/**
 * Store / store view / website delete form container
 */
class Delete extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_object_id = 'item_id';
        $this->_mode = 'delete';
        $this->_block_group = 'Magento_Backend';
        $this->_controller = 'system_store';
        parent::_construct();
        $this->button_list->remove('save');
        $this->button_list->remove('reset');
        $this->button_list->update('delete', 'region', 'toolbar');
        $this->button_list->update('delete', 'onclick', null);
        $this->button_list->update('delete', 'data_attribute', ['mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']]]);
        $this->button_list->add('cancel', ['label' => __('Cancel'), 'onclick' => 'setLocation(\'' . $this->get_back_url() . '\')'], 2, 100, 'toolbar');
    }
    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function get_header_text()
    {
        return __("Delete %1 '%2'", $this->get_store_type_title(), $this->escape_html($this->get_child_block('form')->get_data_object()->get_name()));
    }
    /**
     * Set store type title
     *
     * @param string $title
     * @return $this
     */
    public function set_store_type_title($title)
    {
        $this->button_list->update('delete', 'label', __('Delete %1', $title));
        return $this->set_data('store_type_title', $title);
    }
    /**
     * Set back URL for "Cancel" and "Back" buttons
     *
     * @param string $url
     * @return $this
     */
    public function set_back_url($url)
    {
        $this->set_data('back_url', $url);
        $this->button_list->update('cancel', 'onclick', "setLocation('" . $url . "')");
        $this->button_list->update('back', 'onclick', "setLocation('" . $url . "')");
        return $this;
    }
}