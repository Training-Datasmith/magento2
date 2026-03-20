<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Form\Renderer;

use Magento\Framework\Data\Form\Element\Abstract_Element;
use Magento\Framework\Data\Form\Element\Renderer\Renderer_Interface;
/**
 * Form fieldset default renderer
 *
 * @api
 * @since 100.0.2
 */
class Fieldset extends \Magento\Backend\Block\Template implements Renderer_Interface
{
    /**
     * @var AbstractElement
     */
    protected $_element;
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form/renderer/fieldset.phtml';
    /**
     * Get element
     *
     * @return AbstractElement
     */
    public function get_element()
    {
        return $this->_element;
    }
    /**
     * Render the element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(Abstract_Element $element)
    {
        $this->_element = $element;
        return $this->to_html();
    }
}