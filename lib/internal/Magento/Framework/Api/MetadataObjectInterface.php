<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Provides metadata about an attribute.
 *
 * @api
 * @since 100.0.2
 */
interface Metadata_Object_Interface
{
    /**
     * Retrieve code of the attribute.
     *
     * @return string
     */
    public function get_attribute_code();
    /**
     * Set code of the attribute.
     *
     * @param string $attributeCode
     * @return $this
     */
    public function set_attribute_code($attribute_code);
}