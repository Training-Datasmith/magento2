<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Interface ProductPriceOptionsInterface
 *
 * @api
 */
interface ProductPriceOptionsInterface extends OptionSourceInterface
{
    /**#@+
     * Values
     */
    public const VALUE_FIXED = 'fixed';
    public const VALUE_PERCENT = 'percent';
    /**#@-*/
}
