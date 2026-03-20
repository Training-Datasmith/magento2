<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Interface for custom attribute value.
 *
 * @api
 * @since 100.0.2
 */
interface Attribute_Interface
{
    /**#@+
     * Constant used as key into $_data
     */
    public const ATTRIBUTE_CODE = 'attribute_code';
    public const VALUE = 'value';
    /**#@-*/
    /**
     * Get attribute code
     *
     * @return string
     */
    public function get_attribute_code();
    /**
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     */
    public function set_attribute_code($attribute_code);
    /**
     * Get attribute value
     *
     * @return mixed
     */
    public function get_value();
    /**
     * Set attribute value
     *
     * @param mixed $value
     * @return $this
     */
    public function set_value($value);
}