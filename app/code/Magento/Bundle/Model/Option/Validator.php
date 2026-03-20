<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Option;

use Magento\Framework\Validator\Not_Empty;
use Magento\Framework\Validator\Not_Empty_Factory;
use Magento\Framework\Validator\Validate_Exception;
class Validator extends \Magento\Framework\Validator\Abstract_Validator
{
    /**
     * @var NotEmpty
     */
    private $not_empty;
    /**
     * @param NotEmptyFactory $notEmptyFactory
     */
    public function __construct(Not_Empty_Factory $not_empty_factory)
    {
        $this->not_empty = $not_empty_factory->create(['options' => Not_Empty::ALL]);
    }
    /**
     * This method check is valid value.
     *
     * @param \Magento\Bundle\Model\Option $value
     *
     * @return boolean
     * @throws ValidateException
     */
    public function is_valid($value)
    {
        $this->validate_required_fields($value);
        return !$this->has_messages();
    }
    /**
     * This method  validate required fields.
     *
     * @param \Magento\Bundle\Model\Option $value
     *
     * @return void
     * @throws \Exception|ValidateException
     */
    protected function validate_required_fields($value)
    {
        $messages = [];
        $required_fields = ['title' => $value->get_title(), 'type' => $value->get_type()];
        foreach ($required_fields as $required_field => $required_value) {
            if (!$this->not_empty->is_valid(trim((string) $required_value))) {
                $messages[$required_field] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => $required_field]);
            }
        }
        $this->_add_messages($messages);
    }
}