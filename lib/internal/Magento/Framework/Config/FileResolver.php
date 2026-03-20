<?php

declare (strict_types=1);
/**
 * Application config file resolver
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader as DirReader;
use Magento\Framework\View\Design\Fallback\Rule_Pool;
use Magento\Framework\View\Design\File_Resolution\Fallback\Resolver_Interface;
use Magento\Framework\View\Design\Theme_Interface;
use Magento\Framework\View\Design_Interface;
/**
 * Class FileResolver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class File_Resolver implements \Magento\Framework\Config\File_Resolver_Interface, Design_Resolver_Interface
{
    /**
     * Module configuration file reader
     *
     * @var DirReader
     */
    protected $module_reader;
    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iterator_factory;
    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $current_theme;
    /**
     * @var string
     */
    protected $area;
    /**
     * @var Filesystem\Directory\ReadInterface
     */
    protected $root_directory;
    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface
     */
    protected $resolver;
    /**
     * @var DirectoryList
     * @deprecated 102.0.0 Unused class property
     */
    private $directory_list;
    /**
     * @param DirReader $moduleReader
     * @param FileIteratorFactory $iteratorFactory
     * @param DesignInterface $designInterface
     * @param DirectoryList $directoryList @deprecated
     * @param Filesystem $filesystem
     * @param ResolverInterface $resolver
     */
    public function __construct(Dir_Reader $module_reader, File_Iterator_Factory $iterator_factory, Design_Interface $design_interface, Directory_List $directory_list, Filesystem $filesystem, Resolver_Interface $resolver)
    {
        $this->directory_list = $directory_list;
        $this->iterator_factory = $iterator_factory;
        $this->module_reader = $module_reader;
        $this->current_theme = $design_interface->get_design_theme();
        $this->area = $design_interface->get_area();
        $this->root_directory = $filesystem->get_directory_read(Directory_List::ROOT);
        $this->resolver = $resolver;
    }
    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        switch ($scope) {
            case 'global':
                $iterator = $this->module_reader->get_configuration_files($filename)->to_array();
                $theme_config_file = $this->current_theme->get_customization()->get_custom_view_config_path();
                if ($theme_config_file && $this->root_directory->is_exist($this->root_directory->get_relative_path($theme_config_file))) {
                    $iterator[$this->root_directory->get_relative_path($theme_config_file)] = $this->root_directory->read_file($this->root_directory->get_relative_path($theme_config_file));
                } else {
                    $design_path = $this->resolver->resolve(Rule_Pool::TYPE_FILE, 'etc/view.xml', $this->area, $this->current_theme);
                    if (file_exists($design_path)) {
                        try {
                            $design_dom = new \Dom_Document();
                            $design_dom->load($design_path);
                            $iterator[$design_path] = $design_dom->save_xml();
                        } catch (\Exception $e) {
                            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Could not read config file'));
                        }
                    }
                }
                break;
            default:
                $iterator = $this->iterator_factory->create([]);
                break;
        }
        return $iterator;
    }
    /**
     * {@inheritdoc}
     */
    public function get_parents($filename, $scope)
    {
        switch ($scope) {
            case 'global':
                $iterator = $this->module_reader->get_configuration_files($filename)->to_array();
                $design_path = $this->resolver->resolve(Rule_Pool::TYPE_FILE, 'etc/view.xml', $this->area, $this->current_theme);
                if (file_exists($design_path)) {
                    try {
                        $iterator = $this->get_parent_configs($this->current_theme, []);
                    } catch (\Exception $e) {
                        throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Could not read config file'));
                    }
                }
                break;
            default:
                $iterator = $this->iterator_factory->create([]);
                break;
        }
        return $iterator;
    }
    /**
     * Recursively add parent theme configs
     *
     * @param ThemeInterface $theme
     * @param array $iterator
     * @param int $index
     * @return array
     */
    private function get_parent_configs(Theme_Interface $theme, array $iterator, $index = 0)
    {
        if ($theme->get_parent_theme() && $theme->is_physical()) {
            $parent_design_path = $this->resolver->resolve(Rule_Pool::TYPE_FILE, 'etc/view.xml', $this->area, $theme->get_parent_theme());
            $parent_dom = new \Dom_Document();
            $parent_dom->load($parent_design_path);
            $iterator[$index] = $parent_dom->save_xml();
            $iterator = $this->get_parent_configs($theme->get_parent_theme(), $iterator, ++$index);
        }
        return $iterator;
    }
}