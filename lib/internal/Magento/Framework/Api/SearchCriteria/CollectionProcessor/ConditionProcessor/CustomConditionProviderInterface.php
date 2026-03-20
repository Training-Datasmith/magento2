<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api\Search_Criteria\Collection_Processor\Condition_Processor;

use Magento\Framework\Exception\Input_Exception;
/**
 * Provides collections of custom condition processors (CustomConditionInterface)
 *
 * Used to store processors as mapping attributeName => CustomConditionInterface
 * You can use di.xml to configure with any custom conditions you need
 *
 * @api
 */
interface Custom_Condition_Provider_Interface
{
    /**
     * Get custom processor by field name
     *
     * @param string $fieldName
     * @return CustomConditionInterface
     * @throws InputException
     */
    public function get_processor_by_field(string $field_name): Custom_Condition_Interface;
    /**
     * Check if collection has custom processor for given field name
     *
     * @param string $fieldName
     * @return bool
     */
    public function has_processor_for_field(string $field_name): bool;
}