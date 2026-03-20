<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Form password element
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
/**
 * Class Password
 *
 * Password input type
 */
class Password extends Abstract_Element
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
        $this->set_type('password');
        $this->set_ext_type('textfield');
    }
    /**
     * Get field html
     *
     * @return mixed
     */
    public function get_html()
    {
        $this->add_class('input-text admin__control-text');
        return parent::get_html();
    }
}