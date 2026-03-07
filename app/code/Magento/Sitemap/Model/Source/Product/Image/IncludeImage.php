<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Image include policy into sitemap file
 *
 */

namespace Magento\Sitemap\Model\Source\Product\Image;

/**
 * @api
 * @since 100.0.2
 */
class IncludeImage implements \Magento\Framework\Option\ArrayInterface
{
    /**#@+
     * Add Images into Sitemap possible values
     */
    public const INCLUDE_NONE = 'none';

    public const INCLUDE_BASE = 'base';

    public const INCLUDE_ALL = 'all';

    /**#@-*/

    /**
     * Retrieve options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::INCLUDE_NONE => __('None'),
            self::INCLUDE_BASE => __('Base Only'),
            self::INCLUDE_ALL => __('All'),
        ];
    }
}
