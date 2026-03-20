<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Interface for entities which can be extended with custom attributes.
 *
 * @api
 * @since 100.0.2
 */
interface Custom_Attributes_Data_Interface extends Extensible_Data_Interface
{
    /**
     * Array key for custom attributes
     */
    public const CUSTOM_ATTRIBUTES = 'custom_attributes';
    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null
     */
    public function get_custom_attribute($attribute_code);
    /**
     * Set an attribute value for a given attribute code
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     */
    public function set_custom_attribute($attribute_code, $attribute_value);
    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function get_custom_attributes();
    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     */
    public function set_custom_attributes(array $attributes);
}