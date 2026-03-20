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
interface Product_Interface extends \Magento\Framework\Api\Custom_Attributes_Data_Interface
{
    /**#@+
     * public constants defined for keys of  data array
     */
    public const SKU = 'sku';
    public const NAME = 'name';
    public const PRICE = 'price';
    public const WEIGHT = 'weight';
    public const STATUS = 'status';
    public const VISIBILITY = 'visibility';
    public const ATTRIBUTE_SET_ID = 'attribute_set_id';
    public const TYPE_ID = 'type_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const MEDIA_GALLERY = 'media_gallery';
    public const TIER_PRICE = 'tier_price';
    public const ATTRIBUTES = [self::SKU, self::NAME, self::PRICE, self::WEIGHT, self::STATUS, self::VISIBILITY, self::ATTRIBUTE_SET_ID, self::TYPE_ID, self::CREATED_AT, self::UPDATED_AT, self::MEDIA_GALLERY, self::TIER_PRICE];
    /**#@-*/
    /**
     * Product id
     *
     * @return int|null
     */
    public function get_id();
    /**
     * Set product id
     *
     * @param int $id
     * @return $this
     */
    public function set_id($id);
    /**
     * Product sku
     *
     * @return string
     */
    public function get_sku();
    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     */
    public function set_sku($sku);
    /**
     * Product name
     *
     * @return string|null
     */
    public function get_name();
    /**
     * Set product name
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name);
    /**
     * Product attribute set id
     *
     * @return int|null
     */
    public function get_attribute_set_id();
    /**
     * Set product attribute set id
     *
     * @param int $attributeSetId
     * @return $this
     */
    public function set_attribute_set_id($attribute_set_id);
    /**
     * Product price
     *
     * @return float|null
     */
    public function get_price();
    /**
     * Set product price
     *
     * @param float $price
     * @return $this
     */
    public function set_price($price);
    /**
     * Product status
     *
     * @return int|null
     */
    public function get_status();
    /**
     * Set product status
     *
     * @param int $status
     * @return $this
     */
    public function set_status($status);
    /**
     * Product visibility
     *
     * @return int|null
     */
    public function get_visibility();
    /**
     * Set product visibility
     *
     * @param int $visibility
     * @return $this
     */
    public function set_visibility($visibility);
    /**
     * Product type id
     *
     * @return string|null
     */
    public function get_type_id();
    /**
     * Set product type id
     *
     * @param string $typeId
     * @return $this
     */
    public function set_type_id($type_id);
    /**
     * Product created date
     *
     * @return string|null
     */
    public function get_created_at();
    /**
     * Set product created date
     *
     * @param string $createdAt
     * @return $this
     */
    public function set_created_at($created_at);
    /**
     * Product updated date
     *
     * @return string|null
     */
    public function get_updated_at();
    /**
     * Set product updated date
     *
     * @param string $updatedAt
     * @return $this
     */
    public function set_updated_at($updated_at);
    /**
     * Product weight
     *
     * @return float|null
     */
    public function get_weight();
    /**
     * Set product weight
     *
     * @param float $weight
     * @return $this
     */
    public function set_weight($weight);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Extension_Interface $extension_attributes);
    /**
     * Get product links info
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]|null
     */
    public function get_product_links();
    /**
     * Set product links info
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface[] $links
     * @return $this
     */
    public function set_product_links(?array $links = null);
    /**
     * Get list of product options
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]|null
     */
    public function get_options();
    /**
     * Set list of product options
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface[] $options
     * @return $this
     */
    public function set_options(?array $options = null);
    /**
     * Get media gallery entries
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[]|null
     */
    public function get_media_gallery_entries();
    /**
     * Set media gallery entries
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[] $mediaGalleryEntries
     * @return $this
     */
    public function set_media_gallery_entries(?array $media_gallery_entries = null);
    /**
     * Gets list of product tier prices
     *
     * @return \Magento\Catalog\Api\Data\ProductTierPriceInterface[]|null
     */
    public function get_tier_prices();
    /**
     * Sets list of product tier prices
     *
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterface[] $tierPrices
     * @return $this
     */
    public function set_tier_prices(?array $tier_prices = null);
}