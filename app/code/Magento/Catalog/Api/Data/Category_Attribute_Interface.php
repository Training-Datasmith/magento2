<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface Category_Attribute_Interface extends \Magento\Catalog\Api\Data\Eav_Attribute_Interface
{
    public const ENTITY_TYPE_CODE = 'catalog_category';
}