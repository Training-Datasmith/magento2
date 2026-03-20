<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Data_Provider;

use Magento\Advanced_Search\Model\Suggested_Queries_Interface;
use Magento\Search\Model\Query_Interface;
class Suggestions implements Suggested_Queries_Interface
{
    /**
     * @inheritdoc
     */
    public function is_results_count_enabled(): bool
    {
        return false;
    }
    /**
     * @inheritdoc
     */
    public function get_items(Query_Interface $query): array
    {
        return [];
    }
}