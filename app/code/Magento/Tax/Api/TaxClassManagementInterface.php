<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Tax\Api;

/**
 * Interface for managing classes rates.
 * @api
 * @since 100.0.2
 */
interface TaxClassManagementInterface
{
    /**#@+
     * Tax class type.
     */
    public const TYPE_CUSTOMER = 'CUSTOMER';
    public const TYPE_PRODUCT = 'PRODUCT';
    /**#@-*/

    /**
     * Get tax class id
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterface|null $taxClassKey
     * @param string $taxClassType
     * @return int|null
     */
    public function getTaxClassId($taxClassKey, $taxClassType = self::TYPE_PRODUCT);
}
