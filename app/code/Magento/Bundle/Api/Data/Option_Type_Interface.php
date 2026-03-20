<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Api\Data;

/**
 * Interface OptionTypeInterface
 * @api
 * @since 100.0.2
 */
interface Option_Type_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**
     * Get type label
     *
     * @return string
     */
    public function get_label();
    /**
     * Set type label
     *
     * @param string $label
     * @return $this
     */
    public function set_label($label);
    /**
     * Get type code
     *
     * @return string
     */
    public function get_code();
    /**
     * Set type code
     *
     * @param string $code
     * @return $this
     */
    public function set_code($code);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Bundle\Api\Data\OptionTypeExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Bundle\Api\Data\OptionTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Bundle\Api\Data\Option_Type_Extension_Interface $extension_attributes);
}