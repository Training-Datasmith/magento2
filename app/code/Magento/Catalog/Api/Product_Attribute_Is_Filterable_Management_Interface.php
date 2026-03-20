<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Catalog\Api;

/**
 * Intended to allow setting 'is_filterable' property for specific attribute as integer value via REST/SOAP API
 *
 * @api
 */
interface Product_Attribute_Is_Filterable_Management_Interface
{
    /**
     * Retrieve 'is_filterable' property for specific attribute as integer
     *
     * @param string $attributeCode
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(string $attribute_code): int;
    /**
     * Set 'is_filterable' property for specific attribute as integer
     *
     * @param string $attributeCode
     * @param int $isFilterable
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function set(string $attribute_code, int $is_filterable): bool;
}