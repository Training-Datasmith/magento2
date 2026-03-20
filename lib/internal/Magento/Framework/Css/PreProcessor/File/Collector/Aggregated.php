<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\Pre_Processor\File\Collector;

use Magento\Framework\View\Design\Theme_Interface;
use Magento\Framework\View\File\Collector_Interface;
use Magento\Framework\View\File\File_List\Factory;
use Psr\Log\Logger_Interface;
/**
 * Source of layout files aggregated from a theme and its parents according to merging and overriding conventions
 */
class Aggregated implements Collector_Interface
{
    /**
     * @var Factory
     */
    protected $file_list_factory;
    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $library_files;
    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $base_files;
    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $overridden_base_files;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @param Factory $fileListFactory
     * @param CollectorInterface $libraryFiles
     * @param CollectorInterface $baseFiles
     * @param CollectorInterface $overriddenBaseFiles
     * @param LoggerInterface $logger
     */
    public function __construct(Factory $file_list_factory, Collector_Interface $library_files, Collector_Interface $base_files, Collector_Interface $overridden_base_files, Logger_Interface $logger)
    {
        $this->file_list_factory = $file_list_factory;
        $this->library_files = $library_files;
        $this->base_files = $base_files;
        $this->overridden_base_files = $overridden_base_files;
        $this->logger = $logger;
    }
    /**
     * Retrieve files
     *
     * Aggregate source files from modules and a theme and its ancestors
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     * @throws \LogicException
     */
    public function get_files(Theme_Interface $theme, $file_path)
    {
        $list = $this->file_list_factory->create(\Magento\Framework\Css\Pre_Processor\File\File_List\Collator::class);
        $list->add($this->library_files->get_files($theme, $file_path));
        $list->add($this->base_files->get_files($theme, $file_path));
        foreach ($theme->get_inherited_themes() as $current_theme) {
            $files = $this->overridden_base_files->get_files($current_theme, $file_path);
            $list->replace($files);
        }
        $result = $list->get_all();
        if (empty($result)) {
            $this->logger->notice('magento_import returns empty result by path ' . $file_path . ' for theme ' . $theme->get_code());
        }
        return $result;
    }
}