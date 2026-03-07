<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Elasticsearch\Model\Adapter;

/**
 * @api
 * @since 100.1.0
 */
interface FieldMapperInterface
{
    /**#@+
     * Text flags for field mapping context
     */
    public const TYPE_QUERY = 'text';
    public const TYPE_SORT = 'sort';
    public const TYPE_FILTER = 'default';
    /**#@-*/

    /**
     * Get field name
     *
     * @param string $attributeCode
     * @param array $context
     * @return string
     * @since 100.1.0
     */
    public function getFieldName($attributeCode, $context = []);

    /**
     * Get all entity attribute types
     *
     * @param array $context
     * @return array
     * @since 100.1.0
     */
    public function getAllAttributesTypes($context = []);
}
