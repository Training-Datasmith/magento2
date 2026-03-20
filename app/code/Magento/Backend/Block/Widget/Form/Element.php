<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Form;

use Magento\Framework\Data\Form;
/**
 * Form element widget block
 */
class Element extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_element;
    /**
     * @var Form
     */
    protected $_form;
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_form_block;
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form/element.phtml';
    /**
     * Set element and return self
     *
     * @param string $element
     * @return $this
     */
    public function set_element($element)
    {
        $this->_element = $element;
        return $this;
    }
    /**
     * Set form and return self
     *
     * @param Form $form
     * @return $this
     */
    public function set_form($form)
    {
        $this->_form = $form;
        return $this;
    }
    /**
     * Set form block and return self
     *
     * @param \Magento\Framework\DataObject $formBlock
     * @return $this
     */
    public function set_form_block($form_block)
    {
        $this->_form_block = $form_block;
        return $this;
    }
    /**
     * @inheritDoc
     */
    protected function _before_to_html()
    {
        $this->assign('form', $this->_form);
        $this->assign('element', $this->_element);
        $this->assign('formBlock', $this->_form_block);
        return parent::_before_to_html();
    }
}