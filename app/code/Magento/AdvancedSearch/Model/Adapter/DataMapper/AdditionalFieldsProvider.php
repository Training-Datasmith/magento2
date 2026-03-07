<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Model\Adapter\DataMapper;

/**
 * Provide additional fields for data mapper during search indexer
 * Must return array with the following format: [[product id] => [field name1 => value1, ...], ...]
 */
class AdditionalFieldsProvider implements AdditionalFieldsProviderInterface
{
    /**
     * @param AdditionalFieldsProviderInterface[] $fieldsProviders
     */
    public function __construct(private readonly array $fieldsProviders)
    {
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $productIds, $storeId): array
    {
        $fields = [];
        foreach ($this->fieldsProviders as $fieldsProvider) {
            $fields[] = $fieldsProvider->getFields($productIds, $storeId);
        }

        return array_replace_recursive(...$fields);
    }
}
