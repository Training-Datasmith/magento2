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
interface Product_Custom_Option_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**
     * Product text options group.
     */
    public const OPTION_GROUP_TEXT = 'text';
    /**
     * Product file options group.
     */
    public const OPTION_GROUP_FILE = 'file';
    /**
     * Product select options group.
     */
    public const OPTION_GROUP_SELECT = 'select';
    /**
     * Product date options group.
     */
    public const OPTION_GROUP_DATE = 'date';
    /**
     * Product field option type.
     */
    public const OPTION_TYPE_FIELD = 'field';
    /**
     * Product area option type.
     */
    public const OPTION_TYPE_AREA = 'area';
    /**
     * Product file option type.
     */
    public const OPTION_TYPE_FILE = 'file';
    /**
     * Product drop-down option type.
     */
    public const OPTION_TYPE_DROP_DOWN = 'drop_down';
    /**
     * Product radio option type.
     */
    public const OPTION_TYPE_RADIO = 'radio';
    /**
     * Product checkbox option type.
     */
    public const OPTION_TYPE_CHECKBOX = 'checkbox';
    /**
     * Product multiple option type.
     */
    public const OPTION_TYPE_MULTIPLE = 'multiple';
    /**
     * Product date option type.
     */
    public const OPTION_TYPE_DATE = 'date';
    /**
     * Product datetime option type.
     */
    public const OPTION_TYPE_DATE_TIME = 'date_time';
    /**
     * Product time option type.
     */
    public const OPTION_TYPE_TIME = 'time';
    /**
     * Get product SKU
     *
     * @return string
     */
    public function get_product_sku();
    /**
     * Set product SKU
     *
     * @param string $sku
     * @return $this
     */
    public function set_product_sku($sku);
    /**
     * Get option id
     *
     * @return int|null
     */
    public function get_option_id();
    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function set_option_id($option_id);
    /**
     * Get option title
     *
     * @return string
     */
    public function get_title();
    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function set_title($title);
    /**
     * Get option type
     *
     * @return string
     */
    public function get_type();
    /**
     * Set option type
     *
     * @param string $type
     * @return $this
     */
    public function set_type($type);
    /**
     * Get sort order
     *
     * @return int
     */
    public function get_sort_order();
    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function set_sort_order($sort_order);
    /**
     * Get is require
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_is_require();
    /**
     * Set is require
     *
     * @param bool $isRequired
     * @return $this
     */
    public function set_is_require($is_required);
    /**
     * Get price
     *
     * @return float|null
     */
    public function get_price();
    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function set_price($price);
    /**
     * Get price type
     *
     * @return string|null
     */
    public function get_price_type();
    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function set_price_type($price_type);
    /**
     * Get Sku
     *
     * @return string|null
     */
    public function get_sku();
    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
    public function set_sku($sku);
    /**
     * Get File extension
     *
     * @return string|null
     */
    public function get_file_extension();
    /**
     * Set File extension
     *
     * @param string $fileExtension
     * @return $this
     */
    public function set_file_extension($file_extension);
    /**
     * Get Max characters
     *
     * @return int|null
     */
    public function get_max_characters();
    /**
     * Set Max characters
     *
     * @param int $maxCharacters
     * @return $this
     */
    public function set_max_characters($max_characters);
    /**
     * Get Image x size
     *
     * @return int|null
     */
    public function get_image_size_x();
    /**
     * Set Image x size
     *
     * @param int $imageSizeX
     * @return $this
     */
    public function set_image_size_x($image_size_x);
    /**
     * Get Image Y size
     *
     * @return int|null
     */
    public function get_image_size_y();
    /**
     * Set Image Y size
     *
     * @param int $imageSizeY
     * @return $this
     */
    public function set_image_size_y($image_size_y);
    /**
     * Get Values
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface[]|null
     */
    public function get_values();
    /**
     * Set Values
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface[] $values
     * @return $this
     */
    public function set_values(?array $values = null);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Custom_Option_Extension_Interface $extension_attributes);
}