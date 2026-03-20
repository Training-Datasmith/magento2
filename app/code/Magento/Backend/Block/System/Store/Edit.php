<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Serialize\Serializer_Interface;
/**
 * @api
 *
 * Adminhtml store edit
 * @since 100.0.2
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry var
     *
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry = null;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param SerializerInterface|null $serializer
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry, array $data = [], ?Serializer_Interface $serializer = null)
    {
        $this->_core_registry = $registry;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Serializer_Interface::class);
        parent::__construct($context, $data);
    }
    /**
     * Init class
     *
     * @return void
     */
    protected function _construct()
    {
        switch ($this->_core_registry->registry('store_type')) {
            case 'website':
                $this->_object_id = 'website_id';
                $save_label = __('Save Web Site');
                $delete_label = __('Delete Web Site');
                $delete_url = $this->get_url('*/*/deleteWebsite', ['item_id' => $this->_core_registry->registry('store_data')->get_id()]);
                break;
            case 'group':
                $this->_object_id = 'group_id';
                $save_label = __('Save Store');
                $delete_label = __('Delete Store');
                $delete_url = $this->get_url('*/*/deleteGroup', ['item_id' => $this->_core_registry->registry('store_data')->get_id()]);
                break;
            case 'store':
                $this->_object_id = 'store_id';
                $save_label = __('Save Store View');
                $delete_label = __('Delete Store View');
                $delete_url = $this->get_url('*/*/deleteStore', ['item_id' => $this->_core_registry->registry('store_data')->get_id()]);
                break;
            default:
                $save_label = '';
                $delete_label = '';
                $delete_url = '';
        }
        $this->_block_group = 'Magento_Backend';
        $this->_controller = 'system_store';
        parent::_construct();
        $this->button_list->update('save', 'label', $save_label);
        $this->button_list->update('delete', 'label', $delete_label);
        $this->button_list->update('delete', 'onclick', 'setLocation(\'' . $delete_url . '\');');
        if (!$this->_core_registry->registry('store_data')) {
            return;
        }
        if (!$this->_core_registry->registry('store_data')->is_can_delete()) {
            $this->button_list->remove('delete');
        }
        if ($this->_core_registry->registry('store_data')->is_read_only()) {
            $this->button_list->remove('save');
            $this->button_list->remove('reset');
        }
    }
    /**
     * Get Header text
     *
     * @return string
     */
    public function get_header_text()
    {
        $add_label = '';
        $edit_label = '';
        switch ($this->_core_registry->registry('store_type')) {
            case 'website':
                $edit_label = __('Edit Web Site');
                $add_label = __('New Web Site');
                break;
            case 'group':
                $edit_label = __('Edit Store');
                $add_label = __('New Store');
                break;
            case 'store':
                $edit_label = __('Edit Store View');
                $add_label = __('New Store View');
                break;
        }
        return $this->_core_registry->registry('store_action') == 'add' ? $add_label : $edit_label;
    }
    /**
     * Build child form class form name based on value of store_type in registry
     *
     * @return string
     */
    protected function _build_form_class_name()
    {
        return parent::_build_form_class_name() . '\\' . ucwords($this->_core_registry->registry('store_type'));
    }
    /**
     * Get data for store edit
     *
     * @return string
     * @since 100.2.0
     */
    public function get_store_data()
    {
        return $this->serializer->serialize($this->_core_registry->registry('store_data')->get_data());
    }
}