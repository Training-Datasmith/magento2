<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data_Object;

/**
 * Interface \Magento\Framework\DataObject\KeyValueObjectInterface
 *
 * @api
 */
interface Key_Value_Object_Interface
{
    public const KEY = 'key';
    public const VALUE = 'value';
    /**
     * Get object key
     *
     * @return string
     */
    public function get_key();
    /**
     * Set object key
     *
     * @param string $key
     * @return $this
     */
    public function set_key($key);
    /**
     * Get object value
     *
     * @return string
     */
    public function get_value();
    /**
     * Set object value
     *
     * @param string $value
     * @return $this
     */
    public function set_value($value);
}