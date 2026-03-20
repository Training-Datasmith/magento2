<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store\Delete;

use Magento\Backup\Helper\Data as BackupHelper;
use Magento\Framework\App\Object_Manager;
/**
 * Adminhtml cms block edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var BackupHelper
     */
    private $backup;
    /**
     * @inheritDoc
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, array $data = [], ?Backup_Helper $backup = null)
    {
        parent::__construct($context, $registry, $form_factory, $data);
        $this->backup = $backup ?? Object_Manager::get_instance()->get(Backup_Helper::class);
    }
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_id('store_delete_form');
        $this->set_title(__('Block Information'));
    }
    /**
     * @inheritDoc
     */
    protected function _prepare_form()
    {
        $data_object = $this->get_data_object();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_form_factory->create(['data' => ['id' => 'edit_form', 'action' => $this->get_data('action'), 'method' => 'post']]);
        $form->set_html_id_prefix('store_');
        $fieldset = $form->add_fieldset('base_fieldset', ['legend' => __('Backup Options'), 'class' => 'fieldset-wide']);
        $fieldset->add_field('item_id', 'hidden', ['name' => 'item_id', 'value' => $data_object->get_id()]);
        $backup_options = ['0' => __('No')];
        $backup_selected = '0';
        if ($this->backup->is_enabled()) {
            $backup_options['1'] = __('Yes');
            $backup_selected = '1';
        }
        $fieldset->add_field('create_backup', 'select', ['label' => __('Create DB Backup'), 'title' => __('Create DB Backup'), 'name' => 'create_backup', 'options' => $backup_options, 'value' => $backup_selected]);
        $form->set_use_container(true);
        $this->set_form($form);
        return parent::_prepare_form();
    }
}