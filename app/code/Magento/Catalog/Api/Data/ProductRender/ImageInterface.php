<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data\Product_Render;

use Magento\Framework\Api\Extensible_Data_Interface;
/**
 * Product Render image interface.
 *
 * Represents physical characteristics of image, that can be used in product listing or product view
 *
 * @api
 * @since 102.0.0
 */
interface Image_Interface extends Extensible_Data_Interface
{
    /**
     * Set source or external url to the image
     * (attribute src)
     *
     * @param string $url
     * @return void
     * @since 102.0.0
     */
    public function set_url($url);
    /**
     * Retrieve image url
     *
     * @return string
     * @since 102.0.0
     */
    public function get_url();
    /**
     * Retrieve image code
     *
     * Image code shows, where this image can be used: on listing or on view,
     * What size should this image have, etc...
     *
     * @return string
     * @since 102.0.0
     */
    public function get_code();
    /**
     * Set image code
     *
     * @param string $code
     * @return void
     * @since 102.0.0
     */
    public function set_code($code);
    /**
     * Set original image height in px, e.g. 212.21 px
     *
     * @param string $height
     * @return void
     * @since 102.0.0
     */
    public function set_height($height);
    /**
     * Retrieve image height
     *
     * @return float
     * @since 102.0.0
     */
    public function get_height();
    /**
     * Set image width in px
     *
     * @return float
     * @since 102.0.0
     */
    public function get_width();
    /**
     * Set original image width
     *
     * @param string $width
     * @return void
     * @since 102.0.0
     */
    public function set_width($width);
    /**
     * Retrieve image label
     *
     * Image label is short description of this image
     *
     * @return string
     * @since 102.0.0
     */
    public function get_label();
    /**
     * Set image label
     *
     * @param string $label
     * @return void
     * @since 102.0.0
     */
    public function set_label($label);
    /**
     * Retrieve resize width
     *
     * This width is image dimension, which represents the width, that can be used for performance improvements
     *
     * @return float
     * @since 102.0.0
     */
    public function get_resized_width();
    /**
     * Set resized width
     *
     * @param string $width
     * @return void
     * @since 102.0.0
     */
    public function set_resized_width($width);
    /**
     * Set resized height
     *
     * @param string $height
     * @return void
     * @since 102.0.0
     */
    public function set_resized_height($height);
    /**
     * Retrieve resize height
     *
     * @return float
     * @since 102.0.0
     */
    public function get_resized_height();
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface|null
     * @since 102.0.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Render\Image_Extension_Interface $extension_attributes);
}