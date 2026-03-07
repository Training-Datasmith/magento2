<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\I18n\Dictionary\Loader;

/**
 * Dictionary loader interface
 */
interface FileInterface
{
    /**
     * Load dictionary
     *
     * @param string $file
     * @return \Magento\Setup\Module\I18n\Dictionary
     * @throws \InvalidArgumentException
     */
    public function load($file);
}
