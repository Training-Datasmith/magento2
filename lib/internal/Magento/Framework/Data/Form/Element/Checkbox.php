<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
/**
 * Form checkbox element
 */
class Checkbox extends Abstract_Element
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
        $this->set_type('checkbox');
        $this->set_ext_type('checkbox');
    }
    /**
     * Get HTML attributes
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['type', 'title', 'class', 'style', 'checked', 'onclick', 'onchange', 'disabled', 'tabindex', 'data-form-part', 'data-role', 'data-action'];
    }
    /**
     * Get Element HTML
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function get_element_html()
    {
        if ($checked = $this->get_checked()) {
            $this->set_data('checked', true);
        } else {
            $this->unset_data('checked');
        }
        return parent::get_element_html();
    }
    /**
     * Set check status of checkbox
     *
     * @param bool $value
     *
     * @return Checkbox
     */
    public function set_is_checked($value = false)
    {
        $this->set_data('checked', $value);
        return $this;
    }
    /**
     * Return check status of checkbox
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_is_checked()
    {
        return $this->get_data('checked');
    }
}