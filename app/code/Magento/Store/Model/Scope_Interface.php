<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Store\Model;

/**
 * @api
 * @since 100.0.2
 */
interface ScopeInterface
{
    /**#@+
     * Scope types
     */
    public const SCOPE_STORES = 'stores';
    public const SCOPE_GROUPS   = 'groups';
    public const SCOPE_WEBSITES = 'websites';

    public const SCOPE_STORE   = 'store';
    public const SCOPE_GROUP   = 'group';
    public const SCOPE_WEBSITE = 'website';
    /**#@-*/
}
