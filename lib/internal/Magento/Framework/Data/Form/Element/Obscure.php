<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Form text element
 */
namespace Magento\Framework\Data\Form\Element;

class Obscure extends \Magento\Framework\Data\Form\Element\Password
{
    /**
     * @var string
     */
    protected $_obscured_value = '******';
    /**
     * Hide value to make sure it will not show in HTML
     *
     * @param string $index
     * @return string
     */
    public function get_escaped_value($index = null)
    {
        $value = parent::get_escaped_value($index);
        if (!empty($value)) {
            return $this->_obscured_value;
        }
        return $value;
    }
    /**
     * Returns list of html attributes possible to output in HTML
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['type', 'title', 'class', 'style', 'onclick', 'onchange', 'onkeyup', 'disabled', 'readonly', 'maxlength', 'tabindex', 'data-form-part', 'data-role', 'data-action'];
    }
}