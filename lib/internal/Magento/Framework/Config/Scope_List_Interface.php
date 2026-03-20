<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * Config scope list interface.
 *
 * @api
 * @since 100.0.2
 */
interface Scope_List_Interface
{
    /**
     * Retrieve list of all scopes
     *
     * @return string[]
     */
    public function get_all_scopes();
}