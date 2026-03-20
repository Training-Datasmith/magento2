<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Phrase;
/**
 * Label form element.
 */
class Label extends \Magento\Framework\Data\Form\Element\Abstract_Element
{
    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(\Magento\Framework\Data\Form\Element\Factory $factory_element, \Magento\Framework\Data\Form\Element\Collection_Factory $factory_collection, \Magento\Framework\Escaper $escaper, $data = [])
    {
        parent::__construct($factory_element, $factory_collection, $escaper, $data);
        $this->set_type('label');
    }
    /**
     * Retrieve Element HTML
     *
     * @return string
     */
    public function get_element_html()
    {
        $html = $this->get_bold() ? '<div class="control-value special">' : '<div class="control-value">';
        if (is_string($this->get_value()) || $this->get_value() instanceof Phrase) {
            $html .= $this->get_escaped_value();
        }
        $html .= '</div>';
        $html .= $this->get_after_element_html();
        return $html;
    }
}