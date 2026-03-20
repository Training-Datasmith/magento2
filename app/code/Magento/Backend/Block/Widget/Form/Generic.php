<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Form;

/**
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
class Generic extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_form_factory;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, array $data = [])
    {
        $this->_core_registry = $registry;
        $this->_form_factory = $form_factory;
        parent::__construct($context, $data);
    }
}