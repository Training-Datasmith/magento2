<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset;

/**
 * Form fieldset renderer
 * @api
 * @since 100.0.2
 */
class Element extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements \Magento\Framework\Data\Form\Element\Renderer\Renderer_Interface
{
    /**
     * Form element which re-rendering
     *
     * @var \Magento\Framework\Data\Form\Element\Fieldset
     */
    protected $_element;
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::store/switcher/form/renderer/fieldset/element.phtml';
    /**
     * Retrieve an element
     *
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    public function get_element()
    {
        return $this->_element;
    }
    /**
     * Render element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\Abstract_Element $element)
    {
        $this->_element = $element;
        return $this->to_html();
    }
    /**
     * Return html for store switcher hint
     *
     * @return string
     */
    public function get_hint_html()
    {
        /** @var $storeSwitcher \Magento\Backend\Block\Store\Switcher */
        $store_switcher = $this->_layout->get_block_singleton(\Magento\Backend\Block\Store\Switcher::class);
        return $store_switcher->get_hint_html();
    }
}