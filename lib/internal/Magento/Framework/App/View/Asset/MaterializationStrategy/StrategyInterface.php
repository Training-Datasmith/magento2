<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\View\Asset\Materialization_Strategy;

use Magento\Framework\Filesystem\Directory\Write_Interface;
use Magento\Framework\View\Asset;
/**
 * Interface \Magento\Framework\App\View\Asset\MaterializationStrategy\StrategyInterface
 *
 * @api
 */
interface Strategy_Interface
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
    public function publish_file(Write_Interface $source_dir, Write_Interface $target_dir, $source_path, $destination_path);
    /**
     * Whether the strategy can be applied
     *
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    public function is_supported(Asset\Local_Interface $asset);
}