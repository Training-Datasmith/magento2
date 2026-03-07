<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Model\FilterProductCustomAttribute as Filter;
use Magento\Catalog\Model\Product\Attribute\Repository;

class FilterCustomAttribute
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @param Filter $filter
     * @internal param Filter $customAttribute
     */
    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Remove attributes from black list
     *
     * @param Repository $repository
     * @param array $attributes
     * @return \Magento\Framework\Api\MetadataObjectInterface[]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomAttributesMetadata(Repository $repository, array $attributes): array
    {
        $return = [];
        foreach ($attributes as $attribute) {
            $return[$attribute->getAttributeCode()] = $attribute;
        }

        return $this->filter->execute($return);
    }
}
