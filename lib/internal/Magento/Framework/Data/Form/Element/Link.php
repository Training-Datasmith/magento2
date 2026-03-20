<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Magento Form element renderer to display link element
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Link form element widget.
 */
class Link extends Abstract_Element
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureHtmlRenderer
     * @param Random|null $random
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, $data = [], ?Secure_Html_Renderer $secure_html_renderer = null, ?Random $random = null)
    {
        $secure_html_renderer = $secure_html_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        $random = $random ?? Object_Manager::get_instance()->get(Random::class);
        parent::__construct($factory_element, $factory_collection, $escaper, $data, $secure_html_renderer, $random);
        $this->set_type('link');
    }
    /**
     * Generates element html
     *
     * @return string
     */
    public function get_element_html()
    {
        $html = $this->get_before_element_html() . '<a id="' . $this->get_html_id() . '" ' . $this->serialize($this->get_html_attributes()) . $this->_get_ui_id() . '>' . $this->get_escaped_value() . "</a>\n" . $this->get_after_element_html();
        return $html;
    }
    /**
     * Prepare array of anchor attributes
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['charset', 'coords', 'href', 'hreflang', 'rel', 'rev', 'name', 'shape', 'target', 'accesskey', 'class', 'dir', 'lang', 'style', 'tabindex', 'title', 'xml:lang', 'onblur', 'onclick', 'ondblclick', 'onfocus', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onkeydown', 'onkeypress', 'onkeyup', 'data-role', 'data-action'];
    }
}