<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * @api
 * @since 100.0.2
 */
interface Scope_Interface
{
    /**
     * Default scope reference code
     */
    public const SCOPE_DEFAULT = 'default';
    /**
     * Retrieve scope code
     *
     * @return string
     */
    public function get_code();
    /**
     * Get scope identifier
     *
     * @return int
     */
    public function get_id();
    /**
     * Get scope type
     *
     * @return string
     * @since 100.1.0
     */
    public function get_scope_type();
    /**
     * Get scope type name
     *
     * @return string
     * @since 100.1.0
     */
    public function get_scope_type_name();
    /**
     * Get scope name
     *
     * @return string
     * @since 100.1.0
     */
    public function get_name();
}