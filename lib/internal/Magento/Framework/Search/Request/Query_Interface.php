<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Search\Request;

/**
 * Query Interface
 *
 * @api
 * @since 100.0.2
 */
interface QueryInterface
{
    /**
     * #@+ Query Types
     */
    public const TYPE_MATCH = 'matchQuery';

    public const TYPE_BOOL = 'boolQuery';

    public const TYPE_FILTER = 'filteredQuery';

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

    /**
     * Get Boost
     *
     * @return int|null
     */
    public function getBoost();
}
