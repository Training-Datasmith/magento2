<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api;

/**
 * Interface RepositoryInterface must be implemented in new model
 * @api
 * @since 100.0.2
 */
interface Category_Attribute_Repository_Interface extends \Magento\Framework\Api\Metadata_Service_Interface
{
    /**
     * Retrieve all attributes for entity type
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\CategoryAttributeSearchResultsInterface
     */
    public function get_list(\Magento\Framework\Api\Search_Criteria_Interface $search_criteria);
    /**
     * Retrieve specific attribute
     *
     * @param string $attributeCode
     * @return \Magento\Catalog\Api\Data\CategoryAttributeInterface
     */
    public function get($attribute_code);
}