<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Abstract_Form;
/**
 * Form element collection
 */
class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * Elements storage
     *
     * @var array
     */
    private $_elements;
    /**
     * Elements container
     *
     * @var AbstractForm
     */
    private $_container;
    /**
     * Class constructor
     *
     * @param AbstractForm $container
     */
    public function __construct(Abstract_Form $container)
    {
        $this->_elements = [];
        $this->_container = $container;
    }
    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    #[\Return_Type_Will_Change]
    public function getIterator()
    {
        return new \ArrayIterator($this->_elements);
    }
    /**
     * Implementation of \ArrayAccess:offsetSet()
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    #[\Return_Type_Will_Change]
    public function offsetSet($key, $value)
    {
        $this->_elements[$key] = $value;
    }
    /**
     * Implementation of \ArrayAccess:offsetGet()
     *
     * @param mixed $key
     * @return AbstractElement
     */
    #[\Return_Type_Will_Change]
    public function offsetGet($key)
    {
        return $this->_elements[$key];
    }
    /**
     * Implementation of \ArrayAccess:offsetUnset()
     *
     * @param mixed $key
     * @return void
     */
    #[\Return_Type_Will_Change]
    public function offsetUnset($key)
    {
        unset($this->_elements[$key]);
    }
    /**
     * Implementation of \ArrayAccess:offsetExists()
     *
     * @param mixed $key
     * @return boolean
     */
    #[\Return_Type_Will_Change]
    public function offsetExists($key)
    {
        return isset($this->_elements[$key]);
    }
    /**
     * Add element to collection
     *
     * @todo get it straight with $after
     * @param AbstractElement $element
     * @param bool|string $after
     * @return AbstractElement
     */
    public function add(Abstract_Element $element, $after = false)
    {
        // Set the Form for the node
        if ($this->_container->get_form() instanceof Form) {
            $element->set_container($this->_container);
            $element->set_form($this->_container->get_form());
        }
        if ($after === false) {
            $this->_elements[] = $element;
        } elseif ($after === '^') {
            array_unshift($this->_elements, $element);
        } elseif (is_string($after)) {
            $new_order_elements = [];
            foreach ($this->_elements as $index => $curr_element) {
                if ($curr_element->get_id() == $after) {
                    $new_order_elements[] = $curr_element;
                    $new_order_elements[] = $element;
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $this->_elements = array_merge($new_order_elements, array_slice($this->_elements, $index + 1));
                    return $element;
                }
                $new_order_elements[] = $curr_element;
            }
            $this->_elements[] = $element;
        }
        return $element;
    }
    /**
     * Sort elements by values using a user-defined comparison function
     *
     * @param mixed $callback
     * @return $this
     */
    public function usort($callback)
    {
        usort($this->_elements, $callback);
        return $this;
    }
    /**
     * Remove element from collection
     *
     * @param mixed $elementId
     * @return $this
     */
    public function remove($element_id)
    {
        foreach ($this->_elements as $index => $element) {
            if ($element_id == $element->get_id()) {
                unset($this->_elements[$index]);
            }
        }
        // Renumber elements for further correct adding and removing other elements
        $this->_elements = array_merge($this->_elements, []);
        return $this;
    }
    /**
     * Count elements in collection
     *
     * @return int
     */
    #[\Return_Type_Will_Change]
    public function count()
    {
        return count($this->_elements);
    }
    /**
     * Find element by ID
     *
     * @param mixed $elementId
     * @return AbstractElement
     */
    public function search_by_id($element_id)
    {
        foreach ($this->_elements as $element) {
            if ($element->get_id() == $element_id) {
                return $element;
            }
        }
        return null;
    }
}