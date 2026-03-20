<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\View\Asset\Materialization_Strategy;

use Magento\Framework\Filesystem\Directory\Write_Interface;
use Magento\Framework\View\Asset;
class Copy implements Strategy_Interface
{
    /**
     * Publish file
     *
     * @param WriteInterface $sourceDir
     * @param WriteInterface $targetDir
     * @param string $sourcePath
     * @param string $destinationPath
     * @return bool
     */
    public function publish_file(Write_Interface $source_dir, Write_Interface $target_dir, $source_path, $destination_path)
    {
        return $source_dir->copy_file($source_path, $destination_path, $target_dir);
    }
    /**
     * Whether the strategy can be applied
     *
     * @param Asset\LocalInterface $asset
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function is_supported(Asset\Local_Interface $asset)
    {
        return true;
    }
}