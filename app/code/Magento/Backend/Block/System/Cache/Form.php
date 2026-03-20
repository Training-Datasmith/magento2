<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Cache;

/**
 * Cache management form page
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cache_type_list;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, \Magento\Framework\App\Cache\Type_List_Interface $cache_type_list, array $data = [])
    {
        $this->cache_type_list = $cache_type_list;
        parent::__construct($context, $registry, $form_factory, $data);
    }
    /**
     * Initialize cache management form
     *
     * @return $this
     */
    public function init_form()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_form_factory->create();
        $fieldset = $form->add_fieldset('cache_enable', ['legend' => __('Cache Control')]);
        $fieldset->add_field('all_cache', 'select', ['name' => 'all_cache', 'label' => '<strong>' . __('All Cache') . '</strong>', 'value' => 1, 'options' => ['' => __('No change'), 'refresh' => __('Refresh'), 'disable' => __('Disable'), 'enable' => __('Enable')]]);
        foreach ($this->cache_type_list->get_type_labels() as $type => $label) {
            $fieldset->add_field('enable_' . $type, 'checkbox', ['name' => 'enable[' . $type . ']', 'label' => __($label), 'value' => 1, 'checked' => (int) $this->_cache_state->is_enabled($type)]);
        }
        $this->set_form($form);
        return $this;
    }
}