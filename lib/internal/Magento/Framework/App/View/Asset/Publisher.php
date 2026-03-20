<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\View\Asset;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem\Directory\Write_Factory;
use Magento\Framework\View\Asset;
/**
 * A publishing service for view assets
 *
 * @api
 * @since 100.0.2
 */
class Publisher
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;
    /**
     * @var MaterializationStrategy\Factory
     */
    private $materialization_strategy_factory;
    /**
     * @var WriteFactory
     */
    private $write_factory;
    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param MaterializationStrategy\Factory $materializationStrategyFactory
     * @param WriteFactory $writeFactory
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, Materialization_Strategy\Factory $materialization_strategy_factory, Write_Factory $write_factory)
    {
        $this->filesystem = $filesystem;
        $this->materialization_strategy_factory = $materialization_strategy_factory;
        $this->write_factory = $write_factory;
    }
    /**
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    public function publish(Asset\Local_Interface $asset)
    {
        $dir = $this->filesystem->get_directory_read(Directory_List::STATIC_VIEW);
        if ($dir->is_exist($asset->get_path())) {
            return true;
        }
        return $this->publish_asset($asset);
    }
    /**
     * Publish the asset
     *
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    private function publish_asset(Asset\Local_Interface $asset)
    {
        $target_dir = $this->filesystem->get_directory_write(Directory_List::STATIC_VIEW);
        $full_source = $asset->get_source_file();
        $source = basename($full_source);
        $source_dir = $this->write_factory->create(dirname($full_source));
        $destination = $asset->get_path();
        $strategy = $this->materialization_strategy_factory->create($asset);
        return $strategy->publish_file($source_dir, $target_dir, $source, $destination);
    }
}