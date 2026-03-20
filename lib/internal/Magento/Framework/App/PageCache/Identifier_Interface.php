<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Page_Cache;

/**
 * Page unique identifier interface
 */
interface Identifier_Interface
{
    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function get_value();
}