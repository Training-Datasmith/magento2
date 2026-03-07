<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Design\Theme;

/**
 * Factory for theme packages
 */
class ThemePackageFactory
{
    /**
     * Create an instance of ThemePackage
     *
     * @param string $key
     * @param string $path
     *
     * @return ThemePackage
     */
    public function create($key, $path)
    {
        return new ThemePackage($key, $path);
    }
}
