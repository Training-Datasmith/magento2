<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\Pre_Processor;

use Magento\Framework\App\Filesystem\Directory_List;
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
    public function get_materialization_relative_path()
    {
        return Directory_List::TMP_MATERIALIZATION_DIR . '/' . self::TMP_DIR;
    }
}