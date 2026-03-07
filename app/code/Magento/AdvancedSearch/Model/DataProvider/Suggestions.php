<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Model\DataProvider;

use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Search\Model\QueryInterface;

class Suggestions implements SuggestedQueriesInterface
{
    /**
     * @inheritdoc
     */
    public function isResultsCountEnabled(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getItems(QueryInterface $query): array
    {
        return [];
    }
}
