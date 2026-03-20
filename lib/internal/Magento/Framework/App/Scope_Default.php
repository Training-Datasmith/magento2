<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * Class ScopeDefault
 */
class Scope_Default implements Scope_Interface
{
    /**
     * Retrieve scope code
     *
     * @return string
     */
    public function get_code()
    {
        return '';
    }
    /**
     * Get scope identifier
     *
     * @return int
     */
    public function get_id()
    {
        return 0;
    }
    /**
     * Get scope type
     *
     * @return string
     */
    public function get_scope_type()
    {
        return self::SCOPE_DEFAULT;
    }
    /**
     * Get scope type name
     *
     * @return string
     */
    public function get_scope_type_name()
    {
        return 'Default Scope';
    }
    /**
     * Get scope name
     *
     * @return string
     */
    public function get_name()
    {
        return 'Default';
    }
}