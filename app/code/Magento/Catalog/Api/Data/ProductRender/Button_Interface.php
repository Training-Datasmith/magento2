<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data\Product_Render;

use Magento\Framework\Api\Extensible_Data_Interface;
/**
 * Button interface.
 *
 * This interface represents all manner of product buttons: add to cart, add to compare, etc...
 * The buttons describes by this interface should have interaction with backend
 * @api
 * @since 102.0.0
 */
interface Button_Interface extends Extensible_Data_Interface
{
    /**
     * @param string $postData Post data should be serialized (JSON/serialized) string
     * Post data can be empty
     * @return void
     * @since 102.0.0
     */
    public function set_post_data($post_data);
    /**
     * Retrieve post data
     *
     * Post data is serialized data, which represents post params, that should goes on backend, in order
     * to handle product action
     *
     * @return string
     * @since 102.0.0
     */
    public function get_post_data();
    /**
     * Set button end point
     *
     * End point can be represented by any backend url, where button request can be handled
     *
     * @param string $url
     * @return void
     * @since 102.0.0
     */
    public function set_url($url);
    /**
     * Retrieve url, needed to add product to cart
     *
     * @return string
     * @since 102.0.0
     */
    public function get_url();
    /**
     * Required options is flag for options (attributes), without which we cant do actions with a product
     * E.g.: without product size we cant add this product to cart
     *
     * @param bool $requiredOptions
     * @return void
     * @since 102.0.0
     */
    public function set_required_options($required_options);
    /**
     * Retrieve flag whether a product has options or not
     *
     * @return bool
     * @since 102.0.0
     */
    public function has_required_options();
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ButtonExtensionInterface|null
     * @since 102.0.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\ButtonExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Render\Button_Extension_Interface $extension_attributes);
}