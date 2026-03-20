<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Custom Attribute Data object
 */
class Attribute_Value extends Abstract_Simple_Object implements Attribute_Interface
{
    /**
     * Get attribute code
     *
     * @return string
     */
    public function get_attribute_code()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }
    /**
     * Get attribute value
     *
     * @return mixed
     */
    public function get_value()
    {
        return $this->_get(self::VALUE);
    }
    /**
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     */
    public function set_attribute_code($attribute_code)
    {
        $this->_data[self::ATTRIBUTE_CODE] = $attribute_code;
        return $this;
    }
    /**
     * Set attribute value
     *
     * @param mixed $value
     * @return $this
     */
    public function set_value($value)
    {
        $this->_data[self::VALUE] = $value;
        return $this;
    }
}