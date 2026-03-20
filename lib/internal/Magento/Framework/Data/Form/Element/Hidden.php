<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
/**
 * Form hidden element
 */
class Hidden extends Abstract_Element
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
        $this->set_type('hidden');
        $this->set_ext_type('hiddenfield');
    }
    /**
     * Get default HTML
     *
     * @return mixed
     */
    public function get_default_html()
    {
        $html = $this->get_data('default_html');
        if ($html === null) {
            $html = $this->get_element_html();
        }
        return $html;
    }
}