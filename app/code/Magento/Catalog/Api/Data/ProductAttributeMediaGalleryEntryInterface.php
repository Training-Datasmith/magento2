<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\Extensible_Data_Interface;
/**
 * @api
 * @since 100.0.2
 */
interface Product_Attribute_Media_Gallery_Entry_Interface extends Extensible_Data_Interface
{
    public const ID = 'id';
    public const LABEL = 'label';
    public const POSITION = 'position';
    public const DISABLED = 'disabled';
    public const TYPES = 'types';
    public const MEDIA_TYPE = 'media_type';
    public const FILE = 'file';
    public const CONTENT = 'content';
    /**
     * Retrieve gallery entry ID
     *
     * @return int|null
     */
    public function get_id();
    /**
     * Set gallery entry ID
     *
     * @param int $id
     * @return $this
     */
    public function set_id($id);
    /**
     * Get media type
     *
     * @return string
     */
    public function get_media_type();
    /**
     * Set media type
     *
     * @param string $mediaType
     * @return $this
     */
    public function set_media_type($media_type);
    /**
     * Retrieve gallery entry alternative text
     *
     * @return string
     */
    public function get_label();
    /**
     * Set gallery entry alternative text
     *
     * @param string $label
     * @return $this
     */
    public function set_label($label);
    /**
     * Retrieve gallery entry position (sort order)
     *
     * @return int
     */
    public function get_position();
    /**
     * Set gallery entry position (sort order)
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position);
    /**
     * Check if gallery entry is hidden from product page
     *
     * @return bool
     */
    public function is_disabled();
    /**
     * Set whether gallery entry is hidden from product page
     *
     * @param bool $disabled
     * @return $this
     */
    public function set_disabled($disabled);
    /**
     * Retrieve gallery entry image types (thumbnail, image, small_image etc)
     *
     * @return string[]
     */
    public function get_types();
    /**
     * Set gallery entry image types (thumbnail, image, small_image etc)
     *
     * @param string[] $types
     * @return $this
     */
    public function set_types(?array $types = null);
    /**
     * Get file path
     *
     * @return string|null
     */
    public function get_file();
    /**
     * Set file path
     *
     * @param string $file
     * @return $this
     */
    public function set_file($file);
    /**
     * Get media gallery content
     *
     * @return \Magento\Framework\Api\Data\ImageContentInterface|null
     */
    public function get_content();
    /**
     * Set media gallery content
     *
     * @param \Magento\Framework\Api\Data\ImageContentInterface $content
     * @return $this
     */
    public function set_content($content);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Attribute_Media_Gallery_Entry_Extension_Interface $extension_attributes);
}