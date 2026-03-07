<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface CategoryAttributeInterface extends \Magento\Catalog\Api\Data\EavAttributeInterface
{
    public const ENTITY_TYPE_CODE = 'catalog_category';
}
