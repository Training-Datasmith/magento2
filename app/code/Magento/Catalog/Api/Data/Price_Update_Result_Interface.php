<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Interface returned in case of incorrect price passed to efficient price API.
 * @api
 * @since 102.0.0
 */
interface Price_Update_Result_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**#@+
     * Constants
     */
    public const MESSAGE = 'message';
    public const PARAMETERS = 'parameters';
    /**#@-*/
    /**
     * Get error message, that contains description of error occurred during price update.
     *
     * @return string
     * @since 102.0.0
     */
    public function get_message();
    /**
     * Set error message, that contains description of error occurred during price update.
     *
     * @param string $message
     * @return $this
     * @since 102.0.0
     */
    public function set_message($message);
    /**
     * Get parameters, that could be displayed in error message placeholders.
     *
     * @return string[]
     * @since 102.0.0
     */
    public function get_parameters();
    /**
     * Set parameters, that could be displayed in error message placeholders.
     *
     * @param string[] $parameters
     * @return $this
     * @since 102.0.0
     */
    public function set_parameters(array $parameters);
    /**
     * Retrieve existing extension attributes object.
     * If extension attributes do not exist return null.
     *
     * @return \Magento\Catalog\Api\Data\PriceUpdateResultExtensionInterface|null
     * @since 102.0.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\PriceUpdateResultExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Price_Update_Result_Extension_Interface $extension_attributes);
}