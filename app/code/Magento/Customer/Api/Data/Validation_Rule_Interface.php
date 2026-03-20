<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Api\Data;

/**
 * Validation rule interface.
 * @api
 * @since 100.0.2
 */
interface ValidationRuleInterface
{
    /**#@+
     * Constants for keys of data array
     */
    public const NAME = 'name';
    public const VALUE = 'value';
    /**#@-*/

    /**
     * Get validation rule name
     *
     * @return string
     */
    public function getName();

    /**
     * Set validation rule name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get validation rule value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set validation rule value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);
}
