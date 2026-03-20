<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * Template Types interface
 *
 * @deprecated 101.0.0 because of incorrect location
 */
interface Template_Types_Interface
{
    /**
     * Types of template
     */
    public const TYPE_TEXT = 1;
    public const TYPE_HTML = 2;
    /**
     * Return true if template type eq text
     *
     * @return boolean
     */
    public function is_plain();
    /**
     * Getter for template type
     *
     * @return int
     */
    public function get_type();
}