<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Resource_Model\Indexer;

use Magento\Catalog_Inventory\Model\Stock;
use Magento\Framework\DB\Select;
class Default_Inventory_Stock_Status_Query_Processor implements Stock_Status_Query_Processor_Interface
{
    /**
     * Apply stock status filter to the Select
     *
     * @param Select $select
     * @return Select
     */
    public function execute(Select $select): Select
    {
        return $select;
    }
}