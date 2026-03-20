<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Pricing_Import_Export\Model\Import\Advanced_Pricing;

use Magento\Catalog_Import_Export\Model\Import\Product\Row_Validator_Interface;
use Magento\Framework\Validator\Abstract_Validator;
class Validator extends Abstract_Validator implements Row_Validator_Interface
{
    /**
     * @param RowValidatorInterface[] $validators
     */
    public function __construct(protected $validators = [])
    {
    }
    /**
     * Check value is valid
     *
     * @param array $value
     * @return bool
     */
    public function is_valid($value)
    {
        $return_value = true;
        $this->_clear_messages();
        foreach ($this->validators as $validator) {
            if (!$validator->is_valid($value)) {
                $return_value = false;
                $this->_add_messages($validator->get_messages());
            }
        }
        return $return_value;
    }
    /**
     * @inheritdoc
     */
    public function init($context): static
    {
        foreach ($this->validators as $validator) {
            $validator->init($context);
        }
        return $this;
    }
}