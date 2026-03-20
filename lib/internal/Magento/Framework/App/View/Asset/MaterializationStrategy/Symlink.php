<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\View\Asset\Materialization_Strategy;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem\Directory\Write_Interface;
use Magento\Framework\View\Asset;
class Symlink implements Strategy_Interface
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
        return $source_dir->create_symlink($source_path, $destination_path, $target_dir);
    }
    /**
     * Whether the strategy can be applied
     *
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    public function is_supported(Asset\Local_Interface $asset)
    {
        $source_parts = explode('/', $asset->get_source_file() ?? '');
        if (in_array(Directory_List::TMP_MATERIALIZATION_DIR, $source_parts)) {
            return false;
        }
        return true;
    }
}