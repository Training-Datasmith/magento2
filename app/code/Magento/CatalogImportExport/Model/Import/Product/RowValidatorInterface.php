<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogImportExport\Model\Import\Product;

/**
 * Interface RowValidatorInterface
 *
 * @api
 * @since 100.0.2
 */
interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    public const ERROR_INVALID_SCOPE = 'invalidScope';

    public const ERROR_INVALID_WEBSITE = 'invalidWebsite';

    public const ERROR_INVALID_STORE = 'invalidStore';

    public const ERROR_INVALID_ATTR_SET = 'invalidAttrSet';

    public const ERROR_INVALID_TYPE = 'invalidType';

    public const ERROR_INVALID_CATEGORY = 'invalidCategory';

    public const ERROR_VALUE_IS_REQUIRED = 'isRequired';

    public const ERROR_TYPE_CHANGED = 'typeChanged';

    public const ERROR_SKU_IS_EMPTY = 'skuEmpty';

    public const ERROR_NO_DEFAULT_ROW = 'noDefaultRow';

    public const ERROR_CHANGE_TYPE = 'changeProductType';

    public const ERROR_DUPLICATE_SCOPE = 'duplicateScope';

    public const ERROR_DUPLICATE_SKU = 'duplicateSKU';

    public const ERROR_CHANGE_ATTR_SET = 'changeAttrSet';

    public const ERROR_TYPE_UNSUPPORTED = 'productTypeUnsupported';

    public const ERROR_ROW_IS_ORPHAN = 'rowIsOrphan';

    public const ERROR_INVALID_TIER_PRICE_QTY = 'invalidTierPriceOrQty';

    public const ERROR_INVALID_TIER_PRICE_SITE = 'tierPriceWebsiteInvalid';

    public const ERROR_INVALID_TIER_PRICE_GROUP = 'tierPriceGroupInvalid';

    public const ERROR_INVALID_TIER_PRICE_TYPE = 'tierPriceTypeInvalid';

    public const ERROR_TIER_DATA_INCOMPLETE = 'tierPriceDataIsIncomplete';

    public const ERROR_SKU_NOT_FOUND_FOR_DELETE = 'skuNotFoundToDelete';

    public const ERROR_SUPER_PRODUCTS_SKU_NOT_FOUND = 'superProductsSkuNotFound';

    public const ERROR_MEDIA_DATA_INCOMPLETE = 'mediaDataIsIncomplete';

    public const ERROR_INVALID_WEIGHT = 'invalidWeight';

    public const ERROR_EXCEEDED_MAX_LENGTH = 'exceededMaxLength';

    public const ERROR_INVALID_ATTRIBUTE_TYPE = 'invalidAttributeType';

    public const ERROR_INVALID_ATTRIBUTE_DECIMAL = 'invalidAttributeDecimal';

    public const ERROR_ABSENT_REQUIRED_ATTRIBUTE = 'absentRequiredAttribute';

    public const ERROR_INVALID_ATTRIBUTE_OPTION = 'absentAttributeOption';

    public const ERROR_DUPLICATE_UNIQUE_ATTRIBUTE = 'duplicatedUniqueAttribute';

    public const ERROR_INVALID_VARIATIONS_CUSTOM_OPTIONS = 'invalidVariationsCustomOptions';

    public const ERROR_INVALID_MEDIA_URL_OR_PATH = 'invalidMediaUrlPath';

    public const ERROR_MEDIA_URL_NOT_ACCESSIBLE = 'mediaUrlNotAvailable';

    public const ERROR_MEDIA_PATH_NOT_ACCESSIBLE = 'mediaPathNotAvailable';

    public const ERROR_DUPLICATE_URL_KEY = 'duplicatedUrlKey';

    public const ERROR_DUPLICATE_MULTISELECT_VALUES = 'duplicatedMultiselectValues';

    public const ERROR_SKU_MARGINAL_WHITESPACES = 'skuMarginalWhitespaces';

    /**
     * Value that means all entities (e.g. websites, groups etc.)
     */
    public const VALUE_ALL = 'all';

    /**
     * Initialize validator
     *
     * @param \Magento\CatalogImportExport\Model\Import\Product $context
     * @return $this
     */
    public function init($context);
}
