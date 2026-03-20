<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Api\Data;

/**
 * Interface BundleOptionInterface
 * @api
 * @since 100.0.2
 */
interface Bundle_Option_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**
     * Get bundle option id.
     *
     * @return int
     */
    public function get_option_id();
    /**
     * Set bundle option id.
     *
     * @param int $optionId
     * @return int
     */
    public function set_option_id($option_id);
    /**
     * Get bundle option quantity.
     *
     * @return int
     */
    public function get_option_qty();
    /**
     * Set bundle option quantity.
     *
     * @param int $optionQty
     * @return int
     */
    public function set_option_qty($option_qty);
    /**
     * Get bundle option selection ids.
     *
     * @return int[]
     */
    public function get_option_selections();
    /**
     * Set bundle option selection ids.
     *
     * @param int[] $optionSelections
     * @return int[]
     */
    public function set_option_selections(array $option_selections);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Bundle\Api\Data\BundleOptionExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Bundle\Api\Data\BundleOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Bundle\Api\Data\Bundle_Option_Extension_Interface $extension_attributes);
}