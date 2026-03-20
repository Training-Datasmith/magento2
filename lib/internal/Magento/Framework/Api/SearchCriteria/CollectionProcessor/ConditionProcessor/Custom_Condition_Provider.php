<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api\Search_Criteria\Collection_Processor\Condition_Processor;

use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Phrase;
/**
 * Collection of all custom condition processors
 */
class Custom_Condition_Provider implements Custom_Condition_Provider_Interface
{
    /**
     * @var CustomConditionInterface[]
     */
    private $custom_condition_processors;
    /**
     * @param array $customConditionProcessors
     * @throws InputException
     */
    public function __construct(array $custom_condition_processors = [])
    {
        foreach ($custom_condition_processors as $processor) {
            if (!$processor instanceof Custom_Condition_Interface) {
                throw new Input_Exception(new Phrase('Custom processor must implement "%1".', [Custom_Condition_Interface::class]));
            }
        }
        $this->custom_condition_processors = $custom_condition_processors;
    }
    /**
     * Get custom processor by field name
     *
     * @param string $fieldName
     * @return CustomConditionInterface
     * @throws InputException
     */
    public function get_processor_by_field(string $field_name): Custom_Condition_Interface
    {
        if (!$this->has_processor_for_field($field_name)) {
            throw new Input_Exception(new Phrase('Custom processor for field "%1" is absent.', [$field_name]));
        }
        return $this->custom_condition_processors[$field_name];
    }
    /**
     * Check if collection has custom processor for given field name
     *
     * @param string $fieldName
     * @return bool
     */
    public function has_processor_for_field(string $field_name): bool
    {
        return array_key_exists($field_name, $this->custom_condition_processors);
    }
}