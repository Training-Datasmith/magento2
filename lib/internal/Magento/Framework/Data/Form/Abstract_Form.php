<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form;

use Magento\Framework\Data\Form\Element\Abstract_Element;
use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\Data\Form\Element\Collection_Factory;
use Magento\Framework\Data\Form\Element\Column;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Fieldset;
/**
 * Abstract class for form, column and fieldset
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Abstract_Form extends \Magento\Framework\Data_Object
{
    /**
     * Form level elements collection
     *
     * @var Collection
     */
    protected $_elements;
    /**
     * Element type classes
     *
     * @var array
     */
    protected $_types = [];
    /**
     * @var Factory
     */
    protected $_factory_element;
    /**
     * @var CollectionFactory
     */
    protected $_factory_collection;
    /**
     * @var array
     */
    protected $custom_attributes = [];
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param array $data
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, $data = [])
    {
        $this->_factory_element = $factory_element;
        $this->_factory_collection = $factory_collection;
        parent::__construct($data);
        $this->_construct();
    }
    /**
     * Internal constructor, that is called from real constructor
     *
     * Please override this one instead of overriding real __construct constructor
     *
     * @return void
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        //@codingStandardsIgnoreEnd
    }
    /**
     * Add element type
     *
     * @param string $type
     * @param string $className
     * @return $this
     */
    public function add_type($type, $class_name)
    {
        $this->_types[$type] = $class_name;
        return $this;
    }
    /**
     * Get elements collection
     *
     * @return Collection
     */
    public function get_elements()
    {
        if (empty($this->_elements)) {
            $this->_elements = $this->_factory_collection->create(['container' => $this]);
        }
        return $this->_elements;
    }
    /**
     * Disable elements
     *
     * @param boolean $readonly
     * @param boolean $useDisabled
     * @return $this
     */
    public function set_readonly($readonly, $use_disabled = false)
    {
        if ($use_disabled) {
            $this->set_disabled($readonly);
            $this->set_data('readonly_disabled', $readonly);
        } else {
            $this->set_data('readonly', $readonly);
        }
        foreach ($this->get_elements() as $element) {
            $element->set_readonly($readonly, $use_disabled);
        }
        return $this;
    }
    /**
     * Add form element
     *
     * @param AbstractElement $element
     * @param bool|string|null $after
     * @return $this
     */
    public function add_element(Abstract_Element $element, $after = null)
    {
        $element->set_form($this);
        $this->get_elements()->add($element, $after);
        return $this;
    }
    /**
     * Add child element
     *
     * If $after parameter is false - then element adds to end of collection
     * If $after parameter is null - then element adds to befin of collection
     * If $after parameter is string - then element adds after of the element with some id
     *
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param bool|string|null $after
     * @return AbstractElement
     */
    public function add_field($element_id, $type, $config, $after = false)
    {
        if (isset($this->_types[$type])) {
            $type = $this->_types[$type];
        }
        $element = $this->_factory_element->create($type, ['data' => $config]);
        $element->set_id($element_id);
        $this->add_element($element, $after);
        return $element;
    }
    /**
     * Enter description here...
     *
     * @param string $elementId
     * @return $this
     */
    public function remove_field($element_id)
    {
        $this->get_elements()->remove($element_id);
        return $this;
    }
    /**
     * Add fieldset
     *
     * @param string $elementId
     * @param array $config
     * @param bool|string|null $after
     * @param bool $isAdvanced
     * @return Fieldset
     */
    public function add_fieldset($element_id, $config, $after = false, $is_advanced = false)
    {
        $element = $this->_factory_element->create('fieldset', ['data' => $config]);
        $element->set_id($element_id);
        $element->set_advanced($is_advanced);
        $this->add_element($element, $after);
        return $element;
    }
    /**
     * Add column element
     *
     * @param string $elementId
     * @param array $config
     * @return Column
     */
    public function add_column($element_id, $config)
    {
        $element = $this->_factory_element->create('column', ['data' => $config]);
        $element->set_form($this)->set_id($element_id);
        $this->add_element($element);
        return $element;
    }
    /**
     * Convert elements to array
     *
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function convert_to_array(array $arr_attributes = [])
    {
        $res = [];
        $res['config'] = $this->get_data();
        $res['formElements'] = [];
        foreach ($this->get_elements() as $element) {
            $res['formElements'][] = $element->to_array();
        }
        return $res;
    }
    /**
     * Add custom attribute
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function add_custom_attribute($key, $value)
    {
        $this->custom_attributes[$key] = $value;
        return $this;
    }
    /**
     * Convert data into string with defined keys and values
     *
     * @param array $keys
     * @param string $valueSeparator
     * @param string $fieldSeparator
     * @param string $quote
     * @return string
     */
    public function serialize($keys = [], $value_separator = '=', $field_separator = ' ', $quote = '"')
    {
        $data = [];
        if (empty($keys)) {
            $keys = array_keys($this->_data);
        }
        $custom_attributes = array_filter($this->custom_attributes);
        $keys = array_merge($keys, array_keys(array_diff($this->custom_attributes, $custom_attributes)));
        foreach ($this->_data as $key => $value) {
            if (in_array($key, $keys)) {
                $data[] = $key . $value_separator . $quote . $value . $quote;
            }
        }
        foreach ($custom_attributes as $key => $value) {
            $data[] = $key . $value_separator . $quote . $value . $quote;
        }
        return implode($field_separator, $data);
    }
}