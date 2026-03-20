<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Adapter\Data_Mapper;

/**
 * Provide additional fields for data mapper during search indexer
 * Must return array with the following format: [[product id] => [field name1 => value1, ...], ...]
 */
class Additional_Fields_Provider implements Additional_Fields_Provider_Interface
{
    /**
     * @param AdditionalFieldsProviderInterface[] $fieldsProviders
     */
    public function __construct(private readonly array $fields_providers)
    {
    }
    /**
     * @inheritdoc
     */
    public function get_fields(array $product_ids, $store_id): array
    {
        $fields = [];
        foreach ($this->fields_providers as $fields_provider) {
            $fields[] = $fields_provider->get_fields($product_ids, $store_id);
        }
        return array_replace_recursive(...$fields);
    }
}