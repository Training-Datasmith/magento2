<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Page;

/**
 * Require Js block
 *
 * @api
 * @since 100.0.2
 */
class Require_Js extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $form_key;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Data\Form\Form_Key $form_key, array $data = [])
    {
        parent::__construct($context, $data);
        $this->form_key = $form_key;
    }
    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function get_form_key()
    {
        return $this->form_key->get_form_key();
    }
}