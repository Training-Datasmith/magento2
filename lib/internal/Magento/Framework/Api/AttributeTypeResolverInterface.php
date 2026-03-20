<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Interface Attribute Type Resolver
 *
 * @api
 */
interface Attribute_Type_Resolver_Interface
{
    /**
     * Resolve attribute type
     *
     * @param string $attributeCode
     * @param object $value
     * @param string $context
     * @return string
     */
    public function resolve_object_type($attribute_code, $value, $context);
}