<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Resource_Model\Indexer;

use Magento\Framework\Search\Request\Dimension;
interface Selection_Price_Modifier_Interface
{
    /**
     * Modify selection price data.
     *
     * @param string $indexTable
     * @param Dimension[] $dimensions
     * @return void
     */
    public function modify(string $index_table, array $dimensions): void;
}