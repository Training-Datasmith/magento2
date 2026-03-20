<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\Abstract_Simple_Object;
/**
 * @api
 * @since 100.0.2
 */
class Document extends Abstract_Simple_Object implements Document_Interface, \IteratorAggregate
{
    /**
     * @inheritdoc
     */
    public function get_id()
    {
        return $this->_get(self::ID);
    }
    /**
     * @inheritdoc
     */
    public function set_id($id)
    {
        return $this->set_data(self::ID, $id);
    }
    /**
     * @inheritdoc
     */
    public function get_custom_attribute($attribute_code)
    {
        return $this->_data[self::CUSTOM_ATTRIBUTES][$attribute_code] ?? null;
    }
    /**
     * @inheritdoc
     */
    public function set_custom_attribute($attribute_code, $attribute_value)
    {
        /** @var \Magento\Framework\Api\AttributeInterface[] $attributes */
        $attributes = $this->get_custom_attributes();
        $attributes[$attribute_code] = $attribute_value;
        return $this->set_custom_attributes($attributes);
    }
    /**
     * @inheritdoc
     */
    public function get_custom_attributes()
    {
        return $this->_get(self::CUSTOM_ATTRIBUTES);
    }
    /**
     * @inheritdoc
     */
    public function set_custom_attributes(array $attributes)
    {
        return $this->set_data(self::CUSTOM_ATTRIBUTES, $attributes);
    }
    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     * @since 100.1.0
     */
    #[\Return_Type_Will_Change]
    public function getIterator()
    {
        $attributes = (array) $this->get_custom_attributes();
        return new \ArrayIterator($attributes);
    }
}