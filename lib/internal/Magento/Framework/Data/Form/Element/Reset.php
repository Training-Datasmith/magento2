<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Form relset element
 */
namespace Magento\Framework\Data\Form\Element;

class Reset extends \Magento\Framework\Data\Form\Element\Abstract_Element
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
        $this->set_type('text');
        $this->set_ext_type('textfield');
    }
}