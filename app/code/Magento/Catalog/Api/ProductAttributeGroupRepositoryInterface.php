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
interface Product_Attribute_Group_Repository_Interface
{
    /**
     * Save attribute group
     *
     * @param \Magento\Eav\Api\Data\AttributeGroupInterface $group
     * @return \Magento\Eav\Api\Data\AttributeGroupInterface
     */
    public function save(\Magento\Eav\Api\Data\Attribute_Group_Interface $group);
    /**
     * Retrieve list of attribute groups
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get_list(\Magento\Framework\Api\Search_Criteria_Interface $search_criteria);
    /**
     * Retrieve attribute group
     *
     * @param int $groupId
     * @return \Magento\Eav\Api\Data\AttributeGroupInterface
     */
    public function get($group_id);
    /**
     * Remove attribute group
     *
     * @param \Magento\Eav\Api\Data\AttributeGroupInterface $group
     * @return bool
     */
    public function delete(\Magento\Eav\Api\Data\Attribute_Group_Interface $group);
    /**
     * Remove attribute group by id
     *
     * @param int $groupId
     * @return bool
     */
    public function delete_by_id($group_id);
}