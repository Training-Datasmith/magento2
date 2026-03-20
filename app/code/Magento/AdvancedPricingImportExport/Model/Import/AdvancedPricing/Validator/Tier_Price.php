<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Advanced_Pricing_Import_Export\Model\Import\Advanced_Pricing\Validator;

use Magento\Advanced_Pricing_Import_Export\Model\Import\Advanced_Pricing;
use Magento\Catalog_Import_Export\Model\Import\Product;
use Magento\Catalog_Import_Export\Model\Import\Product\Row_Validator_Interface;
use Magento\Catalog_Import_Export\Model\Import\Product\Validator\Abstract_Price;
use Magento\Customer\Api\Group_Repository_Interface;
use Magento\Framework\Api\Search_Criteria_Builder;
use Magento\Framework\Exception\Localized_Exception;
class Tier_Price extends Abstract_Price
{
    private array $_tier_price_columns = [Advanced_Pricing::COL_TIER_PRICE_WEBSITE, Advanced_Pricing::COL_TIER_PRICE_CUSTOMER_GROUP, Advanced_Pricing::COL_TIER_PRICE_QTY, Advanced_Pricing::COL_TIER_PRICE, Advanced_Pricing::COL_TIER_PRICE_TYPE];
    public function __construct(Group_Repository_Interface $group_repository, Search_Criteria_Builder $search_criteria_builder, protected \Magento\Catalog_Import_Export\Model\Import\Product\Store_Resolver $store_resolver)
    {
        parent::__construct($group_repository, $search_criteria_builder);
    }
    /**
     * Initialize method
     *
     * @param Product $context
     *
     * @throws LocalizedException
     */
    public function init($context): void
    {
        foreach ($this->group_repository->get_list($this->search_criteria_builder->create())->get_items() as $group) {
            $code = $group->get_code();
            if ($code !== null) {
                $this->customer_groups[$code] = $group->get_id();
            }
        }
        $this->context = $context;
    }
    /**
     * Add decimal error
     *
     * @param string $attribute
     *
     * @return void
     */
    protected function add_decimal_error($attribute)
    {
        $this->_add_messages([sprintf($this->context->retrieve_message_template(Row_Validator_Interface::ERROR_INVALID_ATTRIBUTE_DECIMAL), $attribute)]);
    }
    /**
     * Get existing customers groups
     *
     * @return array
     */
    public function get_customer_groups()
    {
        if (!$this->customer_groups) {
            $this->init($this->context);
        }
        return $this->customer_groups;
    }
    /**
     * Validation
     *
     * @param mixed $value
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function is_valid(array $value)
    {
        $this->_clear_messages();
        if (!$this->customer_groups) {
            $this->init($this->context);
        }
        $valid = true;
        if ($this->is_valid_value_and_length($value)) {
            if (!isset($value[Advanced_Pricing::COL_TIER_PRICE_WEBSITE]) || !isset($value[Advanced_Pricing::COL_TIER_PRICE_CUSTOMER_GROUP]) || !isset($value[Advanced_Pricing::COL_TIER_PRICE_QTY]) || !isset($value[Advanced_Pricing::COL_TIER_PRICE]) || !isset($value[Advanced_Pricing::COL_TIER_PRICE_TYPE]) || $this->has_empty_columns($value)) {
                $this->_add_messages([self::ERROR_TIER_DATA_INCOMPLETE]);
                $valid = false;
            } elseif ($value[Advanced_Pricing::COL_TIER_PRICE_CUSTOMER_GROUP] != Advanced_Pricing::VALUE_ALL_GROUPS && !isset($this->customer_groups[$value[Advanced_Pricing::COL_TIER_PRICE_CUSTOMER_GROUP]])) {
                $this->_add_messages([self::ERROR_INVALID_TIER_PRICE_GROUP]);
                $valid = false;
            }
            if ($valid) {
                if (!is_numeric($value[Advanced_Pricing::COL_TIER_PRICE_QTY]) || $value[Advanced_Pricing::COL_TIER_PRICE_QTY] < 0) {
                    $this->add_decimal_error(Advanced_Pricing::COL_TIER_PRICE_QTY);
                    $valid = false;
                }
                if (!is_numeric($value[Advanced_Pricing::COL_TIER_PRICE]) || $value[Advanced_Pricing::COL_TIER_PRICE] < 0) {
                    $this->add_decimal_error(Advanced_Pricing::COL_TIER_PRICE);
                    $valid = false;
                }
            }
        }
        return $valid;
    }
    /**
     * Check if at list one value and length are valid
     *
     *
     * @return bool
     */
    protected function is_valid_value_and_length(array $value)
    {
        $is_valid = false;
        foreach ($this->_tier_price_columns as $column) {
            if (isset($value[$column]) && strlen($value[$column])) {
                $is_valid = true;
            }
        }
        return $is_valid;
    }
    /**
     * Check if value has empty columns
     *
     *
     * @return bool
     */
    protected function has_empty_columns(array $value)
    {
        $has_empty_values = false;
        foreach ($this->_tier_price_columns as $column) {
            if (!strlen((string) $value[$column])) {
                $has_empty_values = true;
            }
        }
        return $has_empty_values;
    }
}