<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 * @since 100.0.2
 */
interface Product_Attribute_Management_Interface
{
    /**
     * Assign attribute to attribute set
     *
     * @param int $attributeSetId
     * @param int $attributeGroupId
     * @param string $attributeCode
     * @param int $sortOrder
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assign($attribute_set_id, $attribute_group_id, $attribute_code, $sort_order);
    /**
     * Remove attribute from attribute set
     *
     * @param string $attributeSetId
     * @param string $attributeCode
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @return bool
     */
    public function unassign($attribute_set_id, $attribute_code);
    /**
     * Retrieve related attributes based on given attribute set ID
     *
     * @param string $attributeSetId
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $attributeSetId is not found
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    public function get_attributes($attribute_set_id);
}