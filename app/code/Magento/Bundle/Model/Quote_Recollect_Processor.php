<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model;

use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Framework\Reflection\Type_Caster;
use Magento\Quote\Model\Resource_Model\Quote as QuoteResource;
/**
 * Recollect quota after handle product relations.
 */
class Quote_Recollect_Processor implements Product_Relations_Processor_Interface
{
    /**
     * @var TypeCaster
     */
    private $type_caster;
    /**
     * @var QuoteResource
     */
    private $quote_resource;
    /**
     * @var array
     */
    private $comparison_fields_type_mapper;
    /**
     * @param TypeCaster $typeCaster
     * @param QuoteResource $quoteResource
     * @param array $comparisonFieldsTypeMapper
     */
    public function __construct(Type_Caster $type_caster, Quote_Resource $quote_resource, array $comparison_fields_type_mapper = [])
    {
        $this->type_caster = $type_caster;
        $this->quote_resource = $quote_resource;
        $this->comparison_fields_type_mapper = $comparison_fields_type_mapper;
    }
    /**
     * Mark quotes to recollect if product options or links are changed.
     *
     * @param ProductInterface $product
     * @param array $existingProductOptions
     * @param array $expectedProductOptions
     * @return void
     */
    public function process(Product_Interface $product, array $existing_product_options, array $expected_product_options): void
    {
        if (empty($existing_product_options)) {
            return;
        }
        if ($this->is_product_options_changed($existing_product_options, $expected_product_options) || $this->is_product_links_changed($existing_product_options, $expected_product_options)) {
            $this->quote_resource->mark_quotes_recollect($product->get_id());
        }
    }
    /**
     * Check product options change.
     *
     * @param array $existingProductOptions
     * @param array $expectedProductOptions
     * @return bool
     */
    private function is_product_options_changed(array $existing_product_options, array $expected_product_options): bool
    {
        if (count($existing_product_options) !== count($expected_product_options)) {
            return true;
        }
        $product_options_diff = array_udiff($expected_product_options, $existing_product_options, function ($expected_product_option, $existing_product_option) {
            if ($expected_product_option->get_option_id() === $existing_product_option->get_option_id()) {
                return $expected_product_option->get_required() - $existing_product_option->get_required();
            }
            return $expected_product_option->get_option_id() - $existing_product_option->get_option_id();
        });
        return (bool) count($product_options_diff);
    }
    /**
     * Check product links change.
     *
     * @param array $existingProductOptions
     * @param array $expectedProductOptions
     * @return bool
     */
    private function is_product_links_changed(array $existing_product_options, array $expected_product_options): bool
    {
        $existing_product_links = $this->flatten_product_links_data($existing_product_options);
        $expected_product_links = $this->flatten_product_links_data($expected_product_options);
        return $existing_product_links != $expected_product_links;
    }
    /**
     * Simplify product links data.
     *
     * @param array $productOptions
     * @return array
     */
    private function flatten_product_links_data(array $product_options): array
    {
        return array_reduce($product_options, function ($result, $product_option) {
            $option_id = $product_option->get_option_id();
            $product_links = [];
            foreach ($product_option->get_product_links() as $product_link) {
                $product_link_data = $product_link->get_data();
                $product_link_filtered_data = [];
                foreach ($this->comparison_fields_type_mapper as $field_name => $field_type) {
                    if (isset($product_link_data[$field_name])) {
                        $product_link_filtered_data[$field_name] = $this->type_caster->cast_value_to_type($product_link_data[$field_name], $field_type);
                    }
                }
                $product_links[$product_link->get_id()] = $product_link_filtered_data;
            }
            $result[$option_id] = $product_links;
            return $result;
        });
    }
}