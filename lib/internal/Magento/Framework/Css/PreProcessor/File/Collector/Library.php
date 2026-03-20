<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\Pre_Processor\File\Collector;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Component\Component_Registrar;
use Magento\Framework\Component\Component_Registrar_Interface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read_Interface;
use Magento\Framework\View\Design\Theme_Interface;
use Magento\Framework\View\File\Collector_Interface;
/**
 * Source of base layout files introduced by modules
 */
class Library implements Collector_Interface
{
    /**
     * @var \Magento\Framework\View\File\Factory
     */
    protected $file_factory;
    /**
     * @var ReadInterface
     */
    protected $library_directory;
    /**
     * @var \Magento\Framework\View\File\FileList\Factory
     */
    protected $file_list_factory;
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $read_factory;
    /**
     * Component registry
     *
     * @var ComponentRegistrarInterface
     */
    private $component_registrar;
    /**
     * @param \Magento\Framework\View\File\FileList\Factory $fileListFactory
     * @param Filesystem $filesystem
     * @param \Magento\Framework\View\File\Factory $fileFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(\Magento\Framework\View\File\File_List\Factory $file_list_factory, Filesystem $filesystem, \Magento\Framework\View\File\Factory $file_factory, \Magento\Framework\Filesystem\Directory\Read_Factory $read_factory, Component_Registrar_Interface $component_registrar)
    {
        $this->file_list_factory = $file_list_factory;
        $this->library_directory = $filesystem->get_directory_read(Directory_List::LIB_WEB);
        $this->file_factory = $file_factory;
        $this->read_factory = $read_factory;
        $this->component_registrar = $component_registrar;
    }
    /**
     * Retrieve files
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     */
    public function get_files(Theme_Interface $theme, $file_path)
    {
        $list = $this->file_list_factory->create(\Magento\Framework\Css\Pre_Processor\File\File_List\Collator::class);
        $files = $this->library_directory->search($file_path);
        $list->add($this->create_files($this->library_directory, $theme, $files));
        foreach ($theme->get_inherited_themes() as $current_theme) {
            $theme_full_path = $current_theme->get_full_path();
            $path = $this->component_registrar->get_path(Component_Registrar::THEME, $theme_full_path);
            if (empty($path)) {
                continue;
            }
            $directory_read = $this->read_factory->create($path);
            $found_files = $directory_read->search("web/{$file_path}");
            $list->replace($this->create_files($directory_read, $theme, $found_files));
        }
        return $list->get_all();
    }
    /**
     * @param ReadInterface $reader
     * @param ThemeInterface $theme
     * @param array $files
     * @return array
     */
    protected function create_files(Read_Interface $reader, Theme_Interface $theme, $files)
    {
        $result = [];
        foreach ($files as $file) {
            $filename = $reader->get_absolute_path($file);
            $result[] = $this->file_factory->create($filename, false, $theme);
        }
        return $result;
    }
}