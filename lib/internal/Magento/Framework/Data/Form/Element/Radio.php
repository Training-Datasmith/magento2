<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Form radio element
 */
namespace Magento\Framework\Data\Form\Element;

class Radio extends \Magento\Framework\Data\Form\Element\Abstract_Element
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
        $this->set_type('radio');
        $this->set_ext_type('radio');
    }
}