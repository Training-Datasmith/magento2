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
interface ProductAttributeInterface extends \Magento\Catalog\Api\Data\EavAttributeInterface
{
    public const ENTITY_TYPE_CODE = 'catalog_product';
    public const CODE_HAS_WEIGHT = 'product_has_weight';
    public const CODE_SPECIAL_PRICE = 'special_price';
    public const CODE_PRICE = 'price';
    public const CODE_TIER_PRICE_FIELD_PRICE_QTY = 'price_qty';
    public const CODE_SHORT_DESCRIPTION = 'short_description';
    public const CODE_SEO_FIELD_META_TITLE = 'meta_title';
    public const CODE_STATUS = 'status';
    public const CODE_NAME = 'name';
    public const CODE_SKU = 'sku';
    public const CODE_SEO_FIELD_META_KEYWORD = 'meta_keyword';
    public const CODE_DESCRIPTION = 'description';
    public const CODE_COST = 'cost';
    public const CODE_SEO_FIELD_URL_KEY = 'url_key';
    public const CODE_TIER_PRICE = 'tier_price';
    public const CODE_TIER_PRICE_FIELD_PRICE = 'price';
    public const CODE_TIER_PRICE_FIELD_PERCENTAGE_VALUE = 'percentage_value';
    public const CODE_TIER_PRICE_FIELD_VALUE_TYPE = 'value_type';
    public const CODE_SEO_FIELD_META_DESCRIPTION = 'meta_description';
    public const CODE_WEIGHT = 'weight';

    /**
     * @return \Magento\Eav\Api\Data\AttributeExtensionInterface|null
     * @since 103.0.0
     */
    public function getExtensionAttributes();
}
