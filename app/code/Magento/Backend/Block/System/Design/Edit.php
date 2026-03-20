<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Design;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
/**
 * Edit store design schedule block.
 */
class Edit extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::system/design/edit.phtml';
    /**
     * Application data storage
     *
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry = null;
    /**
     * Escaper for secure output rendering
     *
     * @var Escaper
     */
    private $escaper;
    /**
     * @inheritdoc
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param Escaper|null $escaper
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = [], ?Escaper $escaper = null)
    {
        $this->_core_registry = $registry;
        $this->escaper = $escaper ?? Object_Manager::get_instance()->get(Escaper::class);
        parent::__construct($context, $data);
    }
    /**
     * @inheritdoc
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_id('design_edit');
    }
    /**
     * @inheritdoc
     */
    protected function _prepare_layout()
    {
        $this->get_toolbar()->add_child('back_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Back'), 'onclick' => 'setLocation(\'' . $this->get_url('adminhtml/*/') . '\')', 'class' => 'back']);
        if ($this->get_design_change_id()) {
            $confirm_message = $this->escaper->escape_js($this->escaper->escape_html(__('Are you sure?')));
            $delete_on_click = 'deleteConfirm(\'' . $confirm_message . '\', \'' . $this->get_delete_url() . '\', {data: {}})';
            $this->get_toolbar()->add_child('delete_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Delete'), 'onclick' => $delete_on_click, 'class' => 'delete']);
        }
        $this->get_toolbar()->add_child('save_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Save'), 'class' => 'save primary', 'data_attribute' => ['mage-init' => ['button' => ['event' => 'save', 'target' => '#design-edit-form']]]]);
        return parent::_prepare_layout();
    }
    /**
     * Return design change Id.
     *
     * @return string
     */
    public function get_design_change_id()
    {
        return $this->_core_registry->registry('design')->get_id();
    }
    /**
     * Return delete url.
     *
     * @return string
     */
    public function get_delete_url()
    {
        return $this->get_url('adminhtml/*/delete', ['_current' => true]);
    }
    /**
     * Return save url for edit form.
     *
     * @return string
     */
    public function get_save_url()
    {
        return $this->get_url('adminhtml/*/save', ['_current' => true]);
    }
    /**
     * Return validation url for edit form.
     *
     * @return string
     */
    public function get_validation_url()
    {
        return $this->get_url('adminhtml/*/validate', ['_current' => true]);
    }
    /**
     * Return page header.
     *
     * @return string
     */
    public function get_header()
    {
        if ($this->_core_registry->registry('design')->get_id()) {
            $header = __('Edit Design Change');
        } else {
            $header = __('New Store Design Change');
        }
        return $header;
    }
}