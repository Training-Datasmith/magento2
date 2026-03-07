<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Helper;

/**
 * Catalog Inventory default helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Error codes, that Catalog Inventory module can set to quote or quote items
     */
    public const ERROR_QTY = 1;

    /**
     * Error qty increments
     */
    public const ERROR_QTY_INCREMENTS = 2;
}
