<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Css\PreProcessor;

use Magento\Framework\App\Filesystem\DirectoryList;

class Config
{
    /**
     * Temporary directory prefix
     */
    public const TMP_DIR = 'pub/static';

    /**
     * Returns relative path to materialization directory
     *
     * @return string
     */
    public function getMaterializationRelativePath()
    {
        return DirectoryList::TMP_MATERIALIZATION_DIR . '/' . self::TMP_DIR;
    }
}
