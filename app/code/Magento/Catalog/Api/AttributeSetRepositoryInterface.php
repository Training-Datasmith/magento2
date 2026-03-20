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
interface Attribute_Set_Repository_Interface
{
    /**
     * Save attribute set data
     *
     * @param \Magento\Eav\Api\Data\AttributeSetInterface $attributeSet
     * @return \Magento\Eav\Api\Data\AttributeSetInterface saved attribute set
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException If attribute set is not found
     */
    public function save(\Magento\Eav\Api\Data\Attribute_Set_Interface $attribute_set);
    /**
     * Retrieve list of Attribute Sets
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeSetSearchResultsInterface
     */
    public function get_list(\Magento\Framework\Api\Search_Criteria_Interface $search_criteria);
    /**
     * Retrieve attribute set information based on given ID
     *
     * @param int $attributeSetId
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $attributeSetId is not found
     * @return \Magento\Eav\Api\Data\AttributeSetInterface
     */
    public function get($attribute_set_id);
    /**
     * Remove given attribute set
     *
     * @param \Magento\Eav\Api\Data\AttributeSetInterface $attributeSet
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     */
    public function delete(\Magento\Eav\Api\Data\Attribute_Set_Interface $attribute_set);
    /**
     * Remove attribute set by given ID
     *
     * @param int $attributeSetId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     */
    public function delete_by_id($attribute_set_id);
}