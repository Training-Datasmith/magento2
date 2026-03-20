<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
/**
 * Form note element
 */
class Note extends Abstract_Element
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, $data = [])
    {
        parent::__construct($factory_element, $factory_collection, $escaper, $data);
        $this->set_type('note');
    }
    /**
     * Get element HTML
     *
     * @return string
     */
    public function get_element_html()
    {
        $html = $this->get_before_element_html() . '<div id="' . $this->get_html_id() . '" class="control-value admin__field-value">' . $this->get_text() . '</div>' . $this->get_after_element_html();
        return $html;
    }
}