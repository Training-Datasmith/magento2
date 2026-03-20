<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Base data object for custom attribute metadata
 */
class Attribute_Metadata extends Abstract_Simple_Object implements Metadata_Object_Interface
{
    public const ATTRIBUTE_CODE = 'attribute_code';
    /**
     * Retrieve code of the attribute.
     *
     * @return string|null
     */
    public function get_attribute_code()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }
    /**
     * Set code of the attribute.
     *
     * @param string $attributeCode
     * @return $this
     */
    public function set_attribute_code($attribute_code)
    {
        return $this->set_data(self::ATTRIBUTE_CODE, $attribute_code);
    }
}