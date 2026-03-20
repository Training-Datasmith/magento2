<?php

/**
 * Value interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Config;

/**
 * Interface \Magento\Framework\App\Config\ValueInterface
 *
 * @api
 * @see \Magento\Framework\App\Config\Value
 */
interface Value_Interface
{
    /**
     * Table name
     *
     * @deprecated since it is not used
     */
    public const ENTITY = 'config_data';
    /**
     * Check if config data value was changed
     *
     * @todo this method should be make as protected
     * @return bool
     */
    public function is_value_changed();
    /**
     * Get old value from existing config
     *
     * @return string
     */
    public function get_old_value();
    /**
     * Get value by key for new user data from <section>/groups/<group>/fields/<field>
     *
     * @param string $key
     * @return string
     */
    public function get_fieldset_data_value($key);
}