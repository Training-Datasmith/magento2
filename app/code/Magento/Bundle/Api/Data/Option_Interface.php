<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Api\Data;

/**
 * Interface OptionInterface
 * @api
 * @since 100.0.2
 */
interface Option_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
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
     * @return string|null
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
     * Get is required option
     *
     * @return bool|null
     */
    public function get_required();
    /**
     * Set whether option is required
     *
     * @param bool $required
     * @return $this
     */
    public function set_required($required);
    /**
     * Get input type
     *
     * @return string|null
     */
    public function get_type();
    /**
     * Set input type
     *
     * @param string $type
     * @return $this
     */
    public function set_type($type);
    /**
     * Get option position
     *
     * @return int|null
     */
    public function get_position();
    /**
     * Set option position
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position);
    /**
     * Get product sku
     *
     * @return string|null
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
     * Get product links
     *
     * @return \Magento\Bundle\Api\Data\LinkInterface[]|null
     */
    public function get_product_links();
    /**
     * Set product links
     *
     * @param \Magento\Bundle\Api\Data\LinkInterface[] $productLinks
     * @return $this
     */
    public function set_product_links(?array $product_links = null);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Bundle\Api\Data\OptionExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Bundle\Api\Data\OptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Bundle\Api\Data\Option_Extension_Interface $extension_attributes);
}