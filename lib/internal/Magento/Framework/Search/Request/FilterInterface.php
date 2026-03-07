<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Search\Request;

/**
 * Filter Interface
 *
 * @api
 * @since 100.0.2
 */
interface FilterInterface
{
    /**
     * #@+ Filter Types
     */
    public const TYPE_TERM = 'termFilter';

    public const TYPE_BOOL = 'boolFilter';

    public const TYPE_RANGE = 'rangeFilter';

    public const TYPE_WILDCARD = 'wildcardFilter';

    /**#@-*/

    /**
     * Get Type
     *
     * @return string
     */
    public function getType();

    /**
     * Get Name
     *
     * @return string
     */
    public function getName();
}
