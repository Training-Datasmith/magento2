<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Interface CustomOptionInterface
 * @api
 * @since 100.0.2
 */
interface Custom_Option_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**#@+
     * Constants
     */
    public const OPTION_ID = 'option_id';
    public const OPTION_VALUE = 'option_value';
    /**#@-*/
    /**
     * Get option id
     *
     * @return string
     */
    public function get_option_id();
    /**
     * Set option id
     *
     * @param string $value
     * @return bool
     */
    public function set_option_id($value);
    /**
     * Get option value
     *
     * @return string
     */
    public function get_option_value();
    /**
     * Set option value
     *
     * @param string $value
     * @return bool
     */
    public function set_option_value($value);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\CustomOptionExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CustomOptionExtensionInterface|null $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Custom_Option_Extension_Interface $extension_attributes);
}