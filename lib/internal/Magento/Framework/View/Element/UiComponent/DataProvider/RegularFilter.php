<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;

/**
 * Class RegularFilter
 */
class RegularFilter implements FilterApplierInterface
{
    /**
     * Apply regular filters like collection filters
     *
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     */
    public function apply(Collection $collection, Filter $filter)
    {
        $collection->addFieldToFilter($filter->getField(), [$filter->getConditionType() => $filter->getValue()]);
    }
}
