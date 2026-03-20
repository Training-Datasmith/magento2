<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Filter which can be used by any methods from service layer.
 *
 * @api
 * @codeCoverageIgnore
 * @since 100.0.2
 */
class Filter extends Abstract_Simple_Object
{
    /**#@+
     * Constants for Data Object keys
     */
    public const KEY_FIELD = 'field';
    public const KEY_VALUE = 'value';
    public const KEY_CONDITION_TYPE = 'condition_type';
    /**
     * Get field
     *
     * @return string
     */
    public function get_field()
    {
        return $this->_get(self::KEY_FIELD);
    }
    /**
     * Set field
     *
     * @param string $field
     * @return $this
     */
    public function set_field($field)
    {
        return $this->set_data(self::KEY_FIELD, $field);
    }
    /**
     * Get value
     *
     * @return string
     */
    public function get_value()
    {
        return $this->_get(self::KEY_VALUE);
    }
    /**
     * Set value
     *
     * @param string $value
     * @return $this
     */
    public function set_value($value)
    {
        return $this->set_data(self::KEY_VALUE, $value);
    }
    /**
     * Get condition type
     *
     * @return string|null
     */
    public function get_condition_type()
    {
        return $this->_get(self::KEY_CONDITION_TYPE) ?: 'eq';
    }
    /**
     * Set condition type
     *
     * @param string $conditionType
     * @return $this
     */
    public function set_condition_type($condition_type)
    {
        return $this->set_data(self::KEY_CONDITION_TYPE, $condition_type);
    }
}