<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Form_Key;

use Magento\Framework\Encryption\Helper\Security;
/**
 * @api
 * @since 100.0.2
 */
class Validator
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_form_key;
    /**
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     */
    public function __construct(\Magento\Framework\Data\Form\Form_Key $form_key)
    {
        $this->_form_key = $form_key;
    }
    /**
     * Validate form key
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function validate(\Magento\Framework\App\Request_Interface $request)
    {
        $form_key = $request->get_param('form_key', null);
        return $form_key && Security::compare_strings($form_key, $this->_form_key->get_form_key());
    }
}