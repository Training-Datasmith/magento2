<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Search\Request;

/**
 * Aggregation Bucket Interface
 *
 * @api
 * @since 100.0.2
 */
interface BucketInterface
{
    /**
     * #@+ Bucket Types
     */
    public const TYPE_TERM = 'termBucket';

    public const TYPE_RANGE = 'rangeBucket';

    public const TYPE_DYNAMIC = 'dynamicBucket';

    public const FIELD_VALUE = 'value';

    /**#@-*/

    /**
     * Get Type
     *
     * @return string
     */
    public function getType();

    /**
     * Get Field
     *
     * @return string
     */
    public function getField();

    /**
     * Get Metrics
     *
     * @return array
     */
    public function getMetrics();

    /**
     * Get Name
     *
     * @return string
     */
    public function getName();
}
