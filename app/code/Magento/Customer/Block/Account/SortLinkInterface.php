<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Block\Account;

/**
 * Interface for sortable links.
 * @api
 * @since 101.0.0
 */
interface SortLinkInterface
{
    /**#@+
     * Constant for confirmation status
     */
    public const SORT_ORDER = 'sortOrder';
    /**#@-*/

    /**
     * Get sort order for block.
     *
     * @return int
     * @since 101.0.0
     */
    public function getSortOrder();
}
