<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Utility;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Component\Component_File;
use Magento\Framework\Component\Component_Registrar;
use Magento\Framework\Component\Dir_Search;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Design\Theme\Theme_Package;
use Magento\Framework\View\Design\Theme\Theme_Package_List;
/**
 * A helper to gather specific kind of files in Magento application.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Files
{
    public const INCLUDE_APP_CODE = 1;
    public const INCLUDE_TESTS = 2;
    public const INCLUDE_DEV_TOOLS = 4;
    public const INCLUDE_TEMPLATES = 8;
    public const INCLUDE_LIBS = 16;
    public const INCLUDE_PUB_CODE = 32;
    public const INCLUDE_NON_CLASSES = 64;
    public const INCLUDE_SETUP = 128;
    /**
     * Return as data set
     */
    public const AS_DATA_SET = 1024;
    /**
     * @var ComponentRegistrar
     */
    protected $component_registrar;
    /**
     * @var \Magento\Framework\App\Utility\Files
     */
    protected static $_instance = null;
    /**
     * @var array
     */
    protected static $_cache = [];
    /**
     * @var DirSearch
     */
    private $dir_search;
    /**
     * @var ThemePackageList
     */
    private $theme_package_list;
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var RegexIteratorFactory
     */
    private $regex_iterator_factory;
    /**
     * Constructor
     *
     * @param ComponentRegistrar $componentRegistrar
     * @param DirSearch $dirSearch
     * @param ThemePackageList $themePackageList
     * @param Json|null $serializer
     * @param RegexIteratorFactory|null $regexIteratorFactory
     */
    public function __construct(Component_Registrar $component_registrar, Dir_Search $dir_search, Theme_Package_List $theme_package_list, ?Json $serializer = null, ?Regex_Iterator_Factory $regex_iterator_factory = null)
    {
        $this->component_registrar = $component_registrar;
        $this->dir_search = $dir_search;
        $this->theme_package_list = $theme_package_list;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Json::class);
        $this->regex_iterator_factory = $regex_iterator_factory ?: Object_Manager::get_instance()->get(Regex_Iterator_Factory::class);
    }
    /**
     * Setter for an instance of self
     *
     * Also can unset the current instance, if no arguments are specified
     *
     * @param Files|null $instance
     * @return void
     */
    public static function set_instance(?Files $instance = null)
    {
        self::$_instance = $instance;
    }
    /**
     * Getter for an instance of self
     *
     * @return \Magento\Framework\App\Utility\Files
     * @throws LocalizedException when there is no instance set
     */
    public static function init()
    {
        if (!self::$_instance) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new Localized_Exception(__('Instance is not set yet.'));
        }
        return self::$_instance;
    }
    /**
     * Compose PHPUnit's data sets that contain each file as the first argument
     *
     * @param array $files
     * @return array
     */
    public static function compose_data_sets(array $files)
    {
        $result = [];
        foreach ($files as $file) {
            $key = $file !== null ? str_replace(BP . '/', '', $file) : '';
            $result[$key] = [$file];
        }
        return $result;
    }
    /**
     * Get list of regular expressions for matching test directories in modules
     *
     * @return array
     */
    private function get_module_test_dirs_regex()
    {
        $module_test_dirs = [];
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_dir) {
            $module_test_dirs[] = str_replace('\\', '/', '#' . $module_dir . '/Test#');
        }
        return $module_test_dirs;
    }
    /**
     * Get base path
     *
     * @return string
     */
    public function get_path_to_source()
    {
        return BP;
    }
    /**
     * Returns list of files, where expected to have class declarations
     *
     * @param int $flags
     * @return array
     */
    public function get_php_files($flags = 0)
    {
        // Sets default value
        if ($flags === 0) {
            $flags = self::INCLUDE_APP_CODE | self::INCLUDE_TESTS | self::INCLUDE_DEV_TOOLS | self::INCLUDE_LIBS | self::AS_DATA_SET;
        }
        $key = __METHOD__ . BP . $flags;
        if (!isset(self::$_cache[$key])) {
            $files = array_merge($this->get_app_code_files($flags), $this->get_test_files($flags), $this->get_dev_tools_files($flags), $this->get_template_files($flags), $this->get_library_files($flags), $this->get_pub_files($flags), $this->get_setup_php_files($flags));
            self::$_cache[$key] = $files;
        }
        if ($flags & self::AS_DATA_SET) {
            return self::compose_data_sets(self::$_cache[$key]);
        }
        return self::$_cache[$key];
    }
    /**
     * Return array with all template files
     *
     * @param int $flags
     * @return array
     */
    private function get_template_files($flags)
    {
        if ($flags & self::INCLUDE_TEMPLATES) {
            return $this->get_phtml_files(false, false);
        }
        return [];
    }
    /**
     * Return array with all php files related to library
     *
     * @param int $flags
     * @return array
     */
    private function get_library_files($flags)
    {
        if ($flags & self::INCLUDE_LIBS) {
            $library_exclude_dirs = [];
            foreach ($this->component_registrar->get_paths(Component_Registrar::LIBRARY) as $library_dir) {
                $library_exclude_dirs[] = str_replace('\\', '/', '#' . $library_dir . '/Test#');
                $library_exclude_dirs[] = str_replace('\\', '/', '#' . $library_dir) . '/[\w]+/Test#';
                if (!($flags & self::INCLUDE_NON_CLASSES)) {
                    $library_exclude_dirs[] = str_replace('\\', '/', '#' . $library_dir . '/registration#');
                }
            }
            return $this->get_files_subset($this->component_registrar->get_paths(Component_Registrar::LIBRARY), '*.php', $library_exclude_dirs);
        }
        return [];
    }
    /**
     * Return array with all php files related to pub
     *
     * @param int $flags
     * @return array
     */
    private function get_pub_files($flags)
    {
        if ($flags & self::INCLUDE_PUB_CODE) {
            return array_merge(Glob::glob(BP . '/*.php', Glob::GLOB_NOSORT), Glob::glob(BP . '/pub/*.php', Glob::GLOB_NOSORT));
        }
        return [];
    }
    /**
     * Return array with all php files related to dev tools
     *
     * @param int $flags
     * @return array
     */
    private function get_dev_tools_files($flags)
    {
        if ($flags & self::INCLUDE_DEV_TOOLS) {
            return $this->get_files_subset([BP . '/dev/tools/Magento'], '*.php', []);
        }
        return [];
    }
    /**
     * Return array with all php files related to modules
     *
     * @param int $flags
     * @return array
     */
    private function get_app_code_files($flags)
    {
        if ($flags & self::INCLUDE_APP_CODE) {
            $exclude_paths = [];
            $paths = $this->component_registrar->get_paths(Component_Registrar::MODULE);
            if ($flags & self::INCLUDE_NON_CLASSES) {
                $paths[] = BP . '/app';
            } else {
                foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_dir) {
                    $exclude_paths[] = str_replace('\\', '/', '#' . $module_dir . '/registration.php#');
                    $exclude_paths[] = str_replace('\\', '/', '#' . $module_dir . '/cli_commands.php#');
                }
            }
            return $this->get_files_subset($paths, '*.php', array_merge($this->get_module_test_dirs_regex(), $exclude_paths));
        }
        return [];
    }
    /**
     * Return array with all test files
     *
     * @param int $flags
     * @return array
     */
    private function get_test_files($flags)
    {
        if ($flags & self::INCLUDE_TESTS) {
            $test_dirs = [BP . '/dev/tests', BP . '/setup/src/Magento/Setup/Test'];
            $module_test_dir = [];
            foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_dir) {
                $module_test_dir[] = $module_dir . '/Test';
            }
            $library_test_dirs = [];
            foreach ($this->component_registrar->get_paths(Component_Registrar::LIBRARY) as $library_dir) {
                $library_test_dirs[] = $library_dir . '/Test';
                $library_test_dirs[] = $library_dir . '/*/Test';
            }
            $test_dirs = array_merge($test_dirs, $module_test_dir, $library_test_dirs);
            return self::get_files($test_dirs, '*.php');
        }
        return [];
    }
    /**
     * Returns list of xml files, used by Magento application
     *
     * @return array
     */
    public function get_xml_files()
    {
        return array_merge($this->get_main_config_files(), $this->get_layout_files(), $this->get_page_layout_files(), $this->get_config_files(), $this->get_di_configs(true), $this->get_layout_config_files(), $this->get_page_type_files());
    }
    /**
     * Retrieve all config files, that participate (or have a chance to participate) in composing main config
     *
     * @param bool $asDataSet
     * @return array
     */
    public function get_main_config_files($as_data_set = true)
    {
        $cache_key = __METHOD__ . '|' . implode('|', [$as_data_set]);
        if (!isset(self::$_cache[$cache_key])) {
            $config_xml_paths = [];
            foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_dir) {
                $config_xml_paths[] = $module_dir . '/etc/config.xml';
                // Module DB-specific configs, e.g. config.mysql4.xml
                $config_xml_paths[] = $module_dir . '/etc/config.*.xml';
            }
            $glob_paths = [BP . '/app/etc/config.xml', BP . '/app/etc/*/config.xml'];
            $config_xml_paths = array_merge($glob_paths, $config_xml_paths);
            $files = [];
            foreach ($config_xml_paths as $xml_path) {
                $files[] = glob($xml_path, GLOB_NOSORT);
            }
            self::$_cache[$cache_key] = array_merge([], ...$files);
        }
        if ($as_data_set) {
            return self::compose_data_sets(self::$_cache[$cache_key]);
        }
        return self::$_cache[$cache_key];
    }
    /**
     * Returns list of configuration files, used by Magento application
     *
     * @param string $fileNamePattern
     * @param array $excludedFileNames
     * @param bool $asDataSet
     * @param bool $collectWithContext
     * @return array
     * @codingStandardsIgnoreStart
     */
    public function get_config_files($file_name_pattern = '*.xml', $excluded_file_names = ['wsdl.xml', 'wsdl2.xml', 'wsi.xml'], $as_data_set = true, $collect_with_context = false)
    {
        $cache_key = __METHOD__ . '|' . $this->serializer->serialize([$file_name_pattern, $excluded_file_names, $as_data_set]);
        if (!isset(self::$_cache[$cache_key])) {
            $method = $collect_with_context ? 'collectFilesWithContext' : 'collectFiles';
            $files = $this->dir_search->{$method}(Component_Registrar::MODULE, "/etc/{$file_name_pattern}");
            $files = array_filter($files, function ($file) use ($excluded_file_names, $collect_with_context) {
                /** @var ComponentFile $file */
                return !in_array(basename($collect_with_context ? $file->get_full_path() : $file), $excluded_file_names);
            });
            self::$_cache[$cache_key] = $files;
        }
        if ($as_data_set) {
            return self::compose_data_sets(self::$_cache[$cache_key]);
        }
        return self::$_cache[$cache_key];
    }
    // @codingStandardsIgnoreEnd
    /**
     * Returns list of XML related files, used by Magento application
     *
     * @param string $fileNamePattern
     * @param array $excludedFileNames
     * @param bool $asDataSet
     * @return array
     */
    public function get_xml_catalog_files($file_name_pattern = '*.xsd', $excluded_file_names = [], $as_data_set = true)
    {
        $cache_key = __METHOD__ . '|' . $this->serializer->serialize([$file_name_pattern, $excluded_file_names, $as_data_set]);
        if (!isset(self::$_cache[$cache_key])) {
            $files = $this->get_files_subset($this->component_registrar->get_paths(Component_Registrar::MODULE), $file_name_pattern, []);
            $library_exclude_dirs = [];
            foreach ($this->component_registrar->get_paths(Component_Registrar::LIBRARY) as $library_dir) {
                $library_exclude_dirs[] = str_replace('\\', '/', '#' . $library_dir . '/Test#');
                $library_exclude_dirs[] = str_replace('\\', '/', '#' . $library_dir) . '/[\w]+/Test#';
            }
            $files = array_merge($files, $this->get_files_subset($this->component_registrar->get_paths(Component_Registrar::LIBRARY), $file_name_pattern, $library_exclude_dirs));
            $files = array_merge($files, $this->get_files_subset($this->component_registrar->get_paths(Component_Registrar::THEME), $file_name_pattern, []));
            $files = array_merge($files, $this->get_files_subset($this->component_registrar->get_paths(Component_Registrar::SETUP), $file_name_pattern, []));
            $files = array_filter($files, function ($file) use ($excluded_file_names) {
                return !in_array(basename($file), $excluded_file_names);
            });
            self::$_cache[$cache_key] = $files;
        }
        if ($as_data_set) {
            return self::compose_data_sets(self::$_cache[$cache_key]);
        }
        return self::$_cache[$cache_key];
    }
    /**
     * Returns a list of configuration files found under theme directories.
     *
     * @param string $fileNamePattern
     * @param bool $asDataSet
     * @return array
     */
    public function get_layout_config_files($file_name_pattern = '*.xml', $as_data_set = true)
    {
        $cache_key = __METHOD__ . '|' . implode('|', [$file_name_pattern, $as_data_set]);
        if (!isset(self::$_cache[$cache_key])) {
            self::$_cache[$cache_key] = $this->dir_search->collect_files(Component_Registrar::THEME, "/etc/{$file_name_pattern}");
        }
        if ($as_data_set) {
            return self::compose_data_sets(self::$_cache[$cache_key]);
        }
        return self::$_cache[$cache_key];
    }
    /**
     * Returns list of page configuration and generic layout files, used by Magento application modules
     *
     * An incoming array can contain the following items
     * array (
     *     'namespace'      => 'namespace_name',
     *     'module'         => 'module_name',
     *     'area'           => 'area_name',
     *     'theme'          => 'theme_name',
     *     'include_code'   => true|false,
     *     'include_design' => true|false,
     *     'with_metainfo'  => true|false,
     * )
     *
     * @param array $incomingParams
     * @param bool $asDataSet
     * @return array
     */
    public function get_layout_files($incoming_params = [], $as_data_set = true)
    {
        return $this->get_layout_xml_files('layout', $incoming_params, $as_data_set);
    }
    /**
     * Returns list of page layout files, used by Magento application modules
     *
     * An incoming array can contain the following items
     * array (
     *     'namespace'      => 'namespace_name',
     *     'module'         => 'module_name',
     *     'area'           => 'area_name',
     *     'theme'          => 'theme_name',
     *     'include_code'   => true|false,
     *     'include_design' => true|false,
     *     'with_metainfo'  => true|false,
     * )
     *
     * @param array $incomingParams
     * @param bool $asDataSet
     * @return array
     */
    public function get_page_layout_files($incoming_params = [], $as_data_set = true)
    {
        return $this->get_layout_xml_files('page_layout', $incoming_params, $as_data_set);
    }
    /**
     * Returns list of UI Component files, used by Magento application
     *
     * An incoming array can contain the following items
     * array (
     *     'namespace'      => 'namespace_name',
     *     'module'         => 'module_name',
     *     'area'           => 'area_name',
     *     'theme'          => 'theme_name',
     *     'include_code'   => true|false,
     *     'include_design' => true|false,
     *     'with_metainfo'  => true|false,
     * )
     *
     * @param array $incomingParams
     * @param bool $asDataSet
     * @return array
     */
    public function get_ui_component_xml_files($incoming_params = [], $as_data_set = true)
    {
        return $this->get_layout_xml_files('ui_component', $incoming_params, $as_data_set);
    }
    /**
     * Collect layout files
     *
     * @param string $location
     * @param array $incomingParams
     * @param bool $asDataSet
     * @return array
     */
    protected function get_layout_xml_files($location, $incoming_params = [], $as_data_set = true)
    {
        $params = ['namespace' => '*', 'module' => '*', 'area' => '*', 'theme_path' => '*/*', 'include_code' => true, 'include_design' => true, 'with_metainfo' => false];
        foreach (array_keys($params) as $key) {
            if (isset($incoming_params[$key])) {
                $params[$key] = $incoming_params[$key];
            }
        }
        $cache_key = hash('sha256', $location . '|' . implode('|', $params));
        if (!isset(self::$_cache[__METHOD__][$cache_key])) {
            $files = [];
            if ($params['include_code']) {
                $files = array_merge($files, $this->collect_module_layout_files($params, $location));
            }
            if ($params['include_design']) {
                $files = array_merge($files, $this->collect_theme_layout_files($params, $location));
            }
            self::$_cache[__METHOD__][$cache_key] = $files;
        }
        if ($as_data_set) {
            return self::compose_data_sets(self::$_cache[__METHOD__][$cache_key]);
        }
        return self::$_cache[__METHOD__][$cache_key];
    }
    /**
     * Collect layout files from modules
     *
     * @param array $params
     * @param string $location
     * @return array
     */
    private function collect_module_layout_files(array $params, $location)
    {
        $files = [];
        $area = $params['area'];
        $required_module_name = $params['namespace'] . '_' . $params['module'];
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_name => $module_dir) {
            if ($required_module_name == '*_*' || $module_name == $required_module_name) {
                $module_files = [];
                $this->_accumulate_files_by_patterns([$module_dir . "/view/{$area}/{$location}"], '*.xml', $module_files);
                if ($params['with_metainfo']) {
                    foreach ($module_files as $module_file) {
                        $module_path = $module_dir !== null ? str_replace(DIRECTORY_SEPARATOR, '/', preg_quote($module_dir, '#')) : '';
                        $regex = '#^' . $module_path . '/view/(?P<area>[a-z]+)/layout/(?P<path>.+)$#i';
                        if ($module_file && preg_match($regex, $module_file, $matches)) {
                            $files[] = [[$matches['area'], '', $module_name, $matches['path'], $module_file]];
                        } else {
                            throw new \UnexpectedValueException("Could not parse modular layout file '{$module_file}'");
                        }
                    }
                } else {
                    $files[] = $module_files;
                }
            }
        }
        return array_merge([], ...$files);
    }
    /**
     * Collect layout files from themes
     *
     * @param array $params
     * @param string $location
     * @return array
     */
    private function collect_theme_layout_files(array $params, $location)
    {
        $files = [];
        $area = $params['area'];
        $required_module_name = $params['namespace'] . '_' . $params['module'];
        $theme_path = $params['theme_path'];
        foreach ($this->theme_package_list->get_themes() as $theme) {
            $current_theme_path = $theme->get_path() !== null ? str_replace(DIRECTORY_SEPARATOR, '/', $theme->get_path()) : '';
            $current_theme_code = $theme->get_vendor() . '/' . $theme->get_name();
            if (($area == '*' || $theme->get_area() === $area) && ($theme_path == '*' || $theme_path == '*/*' || $theme_path == $current_theme_code)) {
                $theme_files = [];
                $this->_accumulate_files_by_patterns([$current_theme_path . "/{$required_module_name}/{$location}"], '*.xml', $theme_files);
                if ($params['with_metainfo']) {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $files[] = [array_merge($this->parse_theme_files($theme_files, $current_theme_path, $theme))];
                } else {
                    $files[] = $theme_files;
                }
            }
        }
        return array_merge([], ...$files);
    }
    /**
     * Parse theme layout files
     *
     * @param array $themeFiles
     * @param string $currentThemePath
     * @param ThemePackage $theme
     * @return array
     */
    private function parse_theme_files($theme_files, $current_theme_path, $theme)
    {
        $files = [];
        $regex = '#^' . $current_theme_path . '/(?P<module>[a-z\d]+_[a-z\d]+)/layout/(override/((base/)|(theme/[a-z\d_]+/[a-z\d_]+/)))?' . '(?P<path>.+)$#i';
        foreach ($theme_files as $theme_file) {
            if ($theme_file && preg_match($regex, $theme_file, $matches)) {
                $files[] = [$theme->get_area(), $theme->get_vendor() . '/' . $theme->get_name(), $matches['module'], $matches['path'], $theme_file];
            } else {
                throw new \UnexpectedValueException("Could not parse theme layout file '{$theme_file}'");
            }
        }
        return $files;
    }
    /**
     * Returns list of page_type files, used by Magento application modules
     *
     * An incoming array can contain the following items
     * array (
     *     'namespace'      => 'namespace_name',
     *     'module'         => 'module_name',
     *     'area'           => 'area_name',
     *     'theme'          => 'theme_name',
     * )
     *
     * @param array $incomingParams
     * @param bool $asDataSet
     * @return array
     */
    public function get_page_type_files($incoming_params = [], $as_data_set = true)
    {
        $params = ['namespace' => '*', 'module' => '*', 'area' => '*'];
        foreach (array_keys($params) as $key) {
            if (isset($incoming_params[$key])) {
                $params[$key] = $incoming_params[$key];
            }
        }
        $cache_key = hash('sha256', implode('|', $params));
        if (!isset(self::$_cache[__METHOD__][$cache_key])) {
            self::$_cache[__METHOD__][$cache_key] = self::get_files($this->get_etc_area_paths($params['namespace'], $params['module'], $params['area']), 'page_types.xml');
        }
        if ($as_data_set) {
            return self::compose_data_sets(self::$_cache[__METHOD__][$cache_key]);
        }
        return self::$_cache[__METHOD__][$cache_key];
    }
    /**
     * Get module etc paths for specified area
     *
     * @param string $namespace
     * @param string $module
     * @param string $area
     * @return array
     */
    private function get_etc_area_paths($namespace, $module, $area)
    {
        $etc_area_paths = [];
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_name => $module_dir) {
            $key_info = explode('_', $module_name);
            if ($key_info[0] == $namespace || $namespace == '*') {
                if ($key_info[1] == $module || $module == '*') {
                    $etc_area_paths[] = $module_dir . "/etc/{$area}";
                }
            }
        }
        return $etc_area_paths;
    }
    /**
     * Returns list of Javascript files in Magento
     *
     * @param string $area
     * @param string $themePath
     * @param string $namespace
     * @param string $module
     * @return array
     */
    public function get_js_files($area = '*', $theme_path = '*/*', $namespace = '*', $module = '*')
    {
        $key = $area . $theme_path . $namespace . $module . __METHOD__ . BP;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $module_web_paths = [];
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_name => $module_dir) {
            $key_info = explode('_', $module_name);
            if ($key_info[0] == $namespace || $namespace == '*') {
                if ($key_info[1] == $module || $module == '*') {
                    $module_web_paths[] = $module_dir . "/view/{$area}/web";
                }
            }
        }
        $theme_paths = $this->get_theme_paths($area, $namespace . '_' . $module, '/web');
        $files = self::get_files(array_merge([BP . '/lib/web/{mage,varien}'], $theme_paths, $module_web_paths), '*.js');
        $result = self::compose_data_sets($files);
        self::$_cache[$key] = $result;
        return $result;
    }
    /**
     * Returns list of all theme paths
     *
     * @param string $area
     * @param string $module
     * @param string $subFolder
     * @return array
     */
    private function get_theme_paths($area, $module, $sub_folder)
    {
        $theme_paths = [];
        foreach ($this->theme_package_list->get_themes() as $theme) {
            if ($area == '*' || $theme->get_area() === $area) {
                $theme_paths[] = $theme->get_path() . $sub_folder;
                $theme_paths[] = $theme->get_path() . "/{$module}" . $sub_folder;
            }
        }
        return $theme_paths;
    }
    /**
     * Returns list of Static HTML files in Magento
     *
     * @param string $area
     * @param string $themePath
     * @param string $namespace
     * @param string $module
     * @return array
     */
    public function get_static_html_files($area = '*', $theme_path = '*/*', $namespace = '*', $module = '*')
    {
        $key = $area . $theme_path . $namespace . $module . __METHOD__;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $module_template_paths = [];
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_name => $module_dir) {
            $key_info = explode('_', $module_name);
            if ($key_info[0] == $namespace || $namespace == '*') {
                if ($key_info[1] == $module || $module == '*') {
                    $module_template_paths[] = $module_dir . "/view/{$area}/web/template";
                    $module_template_paths[] = $module_dir . "/view/{$area}/web/templates";
                }
            }
        }
        $theme_paths = $this->get_theme_paths($area, $namespace . '_' . $module, '/web/template');
        $files = self::get_files(array_merge($theme_paths, $module_template_paths), '*.html');
        $result = self::compose_data_sets($files);
        self::$_cache[$key] = $result;
        return $result;
    }
    /**
     * Get list of static view files that are subject of Magento static view files pre-processing system
     *
     * @param string $filePattern
     * @return array
     */
    public function get_static_pre_processing_files($file_pattern = '*')
    {
        $key = __METHOD__ . '|' . $file_pattern;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $area = '*';
        $locale = '*';
        $result = [];
        $module_locale_path = [];
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_dir) {
            $module_locale_path[] = $module_dir . "/view/{$area}/web/i18n/{$locale}";
        }
        $this->accumulate_static_files($area, $file_pattern, $result);
        $this->_accumulate_files_by_patterns($module_locale_path, $file_pattern, $result, '_parseModuleLocaleStatic');
        $this->accumulate_theme_static_files($area, $locale, $file_pattern, $result);
        self::$_cache[$key] = $result;
        return $result;
    }
    /**
     * Accumulate files from themes
     *
     * @param string $area
     * @param string $locale
     * @param string $filePattern
     * @param array $result
     * @return void
     */
    private function accumulate_theme_static_files($area, $locale, $file_pattern, &$result)
    {
        foreach ($this->theme_package_list->get_themes() as $theme_package) {
            $theme_area = $theme_package->get_area();
            if ($area == '*' || $area == $theme_area) {
                $files = [];
                $theme_path = $theme_package->get_path() !== null ? str_replace(DIRECTORY_SEPARATOR, '/', $theme_package->get_path()) : '';
                $paths = [$theme_path . '/web', $theme_path . '/*_*/web', $theme_path . "/web/i18n/{$locale}", $theme_path . "/*_*/web/i18n/{$locale}"];
                $this->_accumulate_files_by_patterns($paths, $file_pattern, $files);
                $regex = '#^' . $theme_path . '/((?P<module>[a-z\d]+_[a-z_\d]+)/)?web/(i18n/(?P<locale>[a-z_]+)/)?(?P<path>.+)$#i';
                foreach ($files as $file) {
                    if ($file && preg_match($regex, $file, $matches)) {
                        $result[] = [$theme_area, $theme_package->get_vendor() . '/' . $theme_package->get_name(), $matches['locale'], $matches['module'], $matches['path'], $file];
                    } else {
                        throw new \UnexpectedValueException("Could not parse theme static file '{$file}'");
                    }
                }
                if (!$files) {
                    $result[] = [$theme_area, $theme_package->get_vendor() . '/' . $theme_package->get_name(), null, null, null, null];
                }
            }
        }
    }
    /**
     * Get all files from static library directory
     *
     * @return array
     */
    public function get_static_library_files()
    {
        $result = [];
        $this->_accumulate_files_by_patterns([BP . '/lib/web'], '*', $result, '_parseLibStatic');
        return $result;
    }
    /**
     * Parse file path from the absolute path of static library
     *
     * @param string $file
     * @param string $path
     * @return string
     */
    protected function _parse_lib_static($file, $path)
    {
        preg_match('/^' . preg_quote("{$path}/lib/web/", '/') . '(.+)$/i', $file, $matches);
        return $matches[1];
    }
    /**
     * Search files by the specified patterns and accumulate them, applying a callback to each found row
     *
     * @param array $patterns
     * @param string $filePattern
     * @param array $result
     * @param bool $subroutine
     * @return void
     */
    protected function _accumulate_files_by_patterns(array $patterns, $file_pattern, array &$result, $subroutine = false)
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', BP);
        foreach (self::get_files($patterns, $file_pattern) as $file) {
            $file = $file !== null ? str_replace(DIRECTORY_SEPARATOR, '/', $file) : '';
            if ($subroutine) {
                $result[] = $this->{$subroutine}($file, $path);
            } else {
                $result[] = $file;
            }
        }
    }
    /**
     * Parse meta-info of a static file in module
     *
     * @deprecated 102.0.4 Replaced with method accumulateStaticFiles()
     *
     * @param string $file
     * @return array
     */
    protected function _parse_module_static($file)
    {
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_name => $module_path) {
            if ($file && preg_match('/^' . preg_quote("{$module_path}/", '/') . 'view\/([a-z]+)\/web\/(.+)$/i', $file, $matches) === 1) {
                list(, $area, $file_path) = $matches;
                return [$area, '', '', $module_name, $file_path, $file];
            }
        }
        return [];
    }
    /**
     * Search static files from all modules by the specified pattern and accumulate meta-info
     *
     * @param string $area
     * @param string $filePattern
     * @param array $result
     * @return void
     */
    private function accumulate_static_files($area, $file_pattern, array &$result)
    {
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_name => $module_dir) {
            $module_web_path = $module_dir . "/view/{$area}/web";
            foreach (self::get_files([$module_web_path], $file_pattern) as $absolute_path) {
                $local_path = $absolute_path !== null ? substr($absolute_path, strlen($module_dir ?? '') + 1) : '';
                if (preg_match('/^view\/([a-z]+)\/web\/(.+)$/i', $local_path, $matches) === 1) {
                    list(, $parsed_area, $parsed_path) = $matches;
                    $result[] = [$parsed_area, '', '', $module_name, $parsed_path, $absolute_path];
                }
            }
        }
    }
    /**
     * Parse meta-info of a localized (translated) static file in module
     *
     * @param string $file
     * @return array
     */
    protected function _parse_module_locale_static($file)
    {
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_name => $module_path) {
            $app_code = preg_quote("{$module_path}/", '/');
            if ($file && preg_match('/^' . $app_code . 'view\/([a-z]+)\/web\/i18n\/([a-z_]+)\/(.+)$/i', $file, $matches) === 1) {
                list(, $area, $locale, $file_path) = $matches;
                return [$area, '', $locale, $module_name, $file_path, $file];
            }
        }
        return [];
    }
    /**
     * Returns list of Javascript files in Magento by certain area
     *
     * @param string $area
     * @return array
     */
    public function get_js_files_for_area($area)
    {
        $key = __METHOD__ . BP . $area;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $view_area_paths = [];
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_dir) {
            $view_area_paths[] = $module_dir . "/view/{$area}";
        }
        $theme_paths = [];
        foreach ($this->theme_package_list->get_themes() as $theme) {
            if ($area == '*' || $theme->get_area() === $area) {
                $theme_paths[] = $theme->get_path();
            }
        }
        $paths = [BP . '/lib/web/varien'];
        $paths = array_merge($paths, $view_area_paths, $theme_paths);
        $files = self::get_files($paths, '*.js');
        if ($area == 'adminhtml') {
            $adminhtml_paths = [BP . '/lib/web/mage/{adminhtml,backend}'];
            $files = array_merge($files, self::get_files($adminhtml_paths, '*.js'));
        } else {
            $frontend_paths = [BP . '/lib/web/mage'];
            /* current structure of /lib/web/mage directory contains frontend javascript in the root,
               backend javascript in subdirectories. That's why script shouldn't go recursive through subdirectories
               to get js files for frontend */
            $files = array_merge($files, self::get_files($frontend_paths, '*.js', false));
        }
        self::$_cache[$key] = $files;
        return $files;
    }
    /**
     * Returns list of Phtml files in Magento app directory.
     *
     * @param bool $withMetaInfo
     * @param bool $asDataSet
     * @return array
     */
    public function get_phtml_files($with_meta_info = false, $as_data_set = true)
    {
        $key = __METHOD__ . (int) $with_meta_info;
        if (!isset(self::$_cache[$key])) {
            $result = [];
            $this->accumulate_module_template_files($with_meta_info, $result);
            $this->accumulate_theme_template_files($with_meta_info, $result);
            self::$_cache[$key] = $result;
        }
        if ($as_data_set) {
            return self::compose_data_sets(self::$_cache[$key]);
        }
        return self::$_cache[$key];
    }
    /**
     * Returns list of db_schema files, used by Magento application.
     *
     * @param string $fileNamePattern
     * @param array $excludedFileNames
     * @param bool $asDataSet
     * @return array
     * @codingStandardsIgnoreStart
     */
    public function get_db_schema_files($file_name_pattern = 'db_schema.xml', $excluded_file_names = [], $as_data_set = true)
    {
        $cache_key = __METHOD__ . '|' . $this->serializer->serialize([$file_name_pattern, $excluded_file_names, $as_data_set]);
        if (!isset(self::$_cache[$cache_key])) {
            $files = $this->dir_search->collect_files(Component_Registrar::MODULE, "/etc/{$file_name_pattern}");
            $files = array_filter($files, function ($file) use ($excluded_file_names) {
                return !in_array(basename($file), $excluded_file_names);
            });
            self::$_cache[$cache_key] = $files;
        }
        if ($as_data_set) {
            return self::compose_data_sets(self::$_cache[$cache_key]);
        }
        return self::$_cache[$cache_key];
    }
    /**
     * Collect templates from themes
     *
     * @param bool $withMetaInfo
     * @param array $result
     * @return void
     */
    private function accumulate_theme_template_files($with_meta_info, array &$result)
    {
        foreach ($this->theme_package_list->get_themes() as $theme) {
            $files = [];
            $this->_accumulate_files_by_patterns([$theme->get_path() . '/*_*/templates'], '*.phtml', $files);
            if ($with_meta_info) {
                $theme_path = $theme->get_path() !== null ? str_replace(DIRECTORY_SEPARATOR, '/', $theme->get_path()) : '';
                $regex = '#^' . $theme_path . '/(?P<module>[a-z\d]+_[a-z\d]+)/templates/(?P<path>.+)$#i';
                foreach ($files as $file) {
                    if ($file && preg_match($regex, $file, $matches)) {
                        $result[] = [$theme->get_area(), $theme->get_vendor() . '/' . $theme->get_name(), $matches['module'], $matches['path'], $file];
                    } else {
                        echo $regex . ' - ' . $file . "\n";
                        throw new \UnexpectedValueException("Could not parse theme template file '{$file}'");
                    }
                }
            } else {
                $result = array_merge($result, $files);
            }
        }
    }
    /**
     * Collect templates from modules
     *
     * @param bool $withMetaInfo
     * @param array $result
     * @return void
     */
    private function accumulate_module_template_files($with_meta_info, array &$result)
    {
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_name => $module_dir) {
            $files = [];
            $this->_accumulate_files_by_patterns([$module_dir . '/view/*/templates'], '*.phtml', $files);
            if ($with_meta_info) {
                $module_path = $module_dir !== null ? str_replace(DIRECTORY_SEPARATOR, '/', preg_quote($module_dir, '#')) : '';
                $regex = '#^' . $module_path . '/view/(?P<area>[a-z]+)/templates/(?P<path>.+)$#i';
                foreach ($files as $file) {
                    if ($file && preg_match($regex, $file, $matches)) {
                        $result[] = [$matches['area'], '', $module_name, $matches['path'], $file];
                    } else {
                        throw new \UnexpectedValueException("Could not parse module template file '{$file}'");
                    }
                }
            } else {
                $result = array_merge($result, $files);
            }
        }
    }
    /**
     * Returns list of email template files
     *
     * @return array
     */
    public function get_email_templates()
    {
        $key = __METHOD__;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $module_email_paths = [];
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_dir) {
            $module_email_paths[] = $module_dir . '/view/email';
        }
        $files = self::get_files($module_email_paths, '*.html');
        $result = self::compose_data_sets($files);
        self::$_cache[$key] = $result;
        return $result;
    }
    /**
     * Return list of all files. The list excludes tool-specific files
     * (e.g. Git, IDE) or temp files (e.g. in "var/").
     *
     * @return array
     */
    public function get_all_files()
    {
        $key = __METHOD__ . BP;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $paths = array_merge([BP . '/app', BP . '/dev', BP . '/lib', BP . '/pub'], $this->component_registrar->get_paths(Component_Registrar::LANGUAGE), $this->component_registrar->get_paths(Component_Registrar::THEME), $this->get_paths());
        $sub_files = self::get_files($paths, '*');
        $root_files = glob(BP . '/*', GLOB_NOSORT);
        $root_files = array_filter($root_files, function ($file) {
            return is_file($file);
        });
        $result = array_merge($root_files, $sub_files);
        $result = self::compose_data_sets($result);
        self::$_cache[$key] = $result;
        return $result;
    }
    /**
     * Retrieve all files in folders and sub-folders that match pattern (glob syntax)
     *
     * @param array $dirPatterns
     * @param string $fileNamePattern
     * @param bool $recursive
     * @return array
     */
    public static function get_files(array $dir_patterns, $file_name_pattern, $recursive = true)
    {
        $result = [];
        foreach ($dir_patterns as $one_dir_pattern) {
            $one_dir_pattern = $one_dir_pattern !== null ? str_replace('\\', '/', $one_dir_pattern) : '';
            $entries_in_dir = Glob::glob("{$one_dir_pattern}/{$file_name_pattern}", Glob::GLOB_NOSORT | Glob::GLOB_BRACE);
            $sub_dirs = Glob::glob("{$one_dir_pattern}/*", Glob::GLOB_ONLYDIR | Glob::GLOB_NOSORT | Glob::GLOB_BRACE);
            $files_in_dir = array_diff($entries_in_dir, $sub_dirs);
            if ($recursive) {
                $files_in_sub_dir = self::get_files($sub_dirs, $file_name_pattern);
                $result = array_merge($result, $files_in_dir, $files_in_sub_dir);
            }
        }
        return $result;
    }
    /**
     * Look for DI config through the system
     *
     * @param bool $asDataSet
     * @return array
     */
    public function get_di_configs($as_data_set = false)
    {
        $primary_configs = Glob::glob(BP . '/app/etc/{di.xml,*/di.xml}', Glob::GLOB_BRACE);
        $module_configs = [];
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $module_dir) {
            $module_configs = array_merge($module_configs, Glob::glob($module_dir . '/etc/{di,*/di}.xml', Glob::GLOB_BRACE));
        }
        $configs = array_merge($primary_configs, $module_configs);
        if ($as_data_set) {
            $output = [];
            foreach ($configs as $file) {
                $output[$file] = [$file];
            }
            return $output;
        }
        return $configs;
    }
    /**
     * Get module and library paths
     *
     * @return array
     */
    private function get_paths()
    {
        $directories = [];
        foreach ($this->component_registrar->get_paths(Component_Registrar::MODULE) as $full_module_dir) {
            $directories[] = $full_module_dir;
        }
        foreach ($this->component_registrar->get_paths(Component_Registrar::LIBRARY) as $library_dir) {
            $directories[] = $library_dir;
        }
        return $directories;
    }
    /**
     * Check if specified class exists
     *
     * @param string $class
     * @param string &$path
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function class_file_exists($class, &$path = '')
    {
        $class = $class ?: '';
        if ($class[0] == '\\') {
            $class = substr($class, 1);
        }
        $class_parts = explode('\\', $class);
        $class_name = array_pop($class_parts);
        $namespace = implode('\\', $class_parts);
        $path = implode('/', explode('\\', $class)) . '.php';
        $directories = ['/dev/tools', '/dev/tests/api-functional/framework', '/dev/tests/setup-integration/framework', '/dev/tests/integration/framework', '/dev/tests/integration/framework/tests/unit/testsuite', '/dev/tests/integration/testsuite', '/dev/tests/integration/testsuite/Magento/Test/Integrity', '/dev/tests/static/framework', '/dev/tests/static/testsuite', '/setup/src'];
        foreach ($directories as $key => $dir) {
            $directories[$key] = BP . $dir;
        }
        $directories = array_merge($directories, $this->get_paths());
        foreach ($directories as $dir) {
            $full_path = $dir . '/' . $path;
            if ($this->class_file_exists_check_content($full_path, $namespace, $class_name)) {
                return true;
            }
            $class_parts = explode('/', $path, 3);
            if (count($class_parts) >= 3) {
                // Check if it's PSR-4 class with trimmed vendor and package name parts
                $trimmed_full_path = $dir . '/' . $class_parts[2];
                if ($this->class_file_exists_check_content($trimmed_full_path, $namespace, $class_name)) {
                    return true;
                }
            }
            $class_parts = explode('/', $path, 4);
            if (count($class_parts) >= 4) {
                // Check if it's a library under framework directory
                $trimmed_full_path = $dir . '/' . $class_parts[3];
                if ($this->class_file_exists_check_content($trimmed_full_path, $namespace, $class_name)) {
                    return true;
                }
                $trimmed_full_path = $dir . '/' . $class_parts[2] . '/' . $class_parts[3];
                if ($this->class_file_exists_check_content($trimmed_full_path, $namespace, $class_name)) {
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Helper function for classFileExists to check file content
     *
     * @param string $fullPath
     * @param string $namespace
     * @param string $className
     * @return bool
     */
    private function class_file_exists_check_content($full_path, $namespace, $class_name)
    {
        /**
         * Use realpath() instead of file_exists() to avoid incorrect work on Windows
         * because of case insensitivity of file names
         * Note that realpath() automatically changes directory separator to the OS-native
         * Since realpath won't work with symlinks we also check file_exists if realpath failed
         */
        if ($full_path && realpath($full_path) == str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $full_path) || file_exists($full_path)) {
            $file_content = file_get_contents($full_path);
            if (strpos($file_content, 'namespace ' . $namespace) !== false && (strpos($file_content, 'class ' . $class_name) !== false || strpos($file_content, 'interface ' . $class_name) !== false || strpos($file_content, 'trait ' . $class_name) !== false)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Return list of declared namespaces
     *
     * @return array
     */
    public function get_namespaces()
    {
        $key = __METHOD__;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $result = [];
        foreach (array_keys($this->component_registrar->get_paths(Component_Registrar::MODULE)) as $module_name) {
            $namespace = explode('_', $module_name)[0];
            if (!in_array($namespace, $result) && $namespace !== 'Zend') {
                $result[] = $namespace;
            }
        }
        self::$_cache[$key] = $result;
        return $result;
    }
    /**
     * @param string $namespace
     * @param string $module
     * @param string $file
     * @return string
     */
    public function get_module_file($namespace, $module, $file)
    {
        return $this->component_registrar->get_path(Component_Registrar::MODULE, $namespace . '_' . $module) . '/' . $file;
    }
    /**
     * Returns array of PHP-files for specified module
     *
     * @param string $module
     * @param bool $asDataSet
     * @return array
     */
    public function get_module_php_files($module, $as_data_set = true)
    {
        $key = __METHOD__ . "/{$module}";
        if (!isset(self::$_cache[$key])) {
            $files = self::get_files([$this->component_registrar->get_path(Component_Registrar::MODULE, 'Magento_' . $module)], '*.php');
            self::$_cache[$key] = $files;
        }
        if ($as_data_set) {
            return self::compose_data_sets(self::$_cache[$key]);
        }
        return self::$_cache[$key];
    }
    /**
     * Returns array of composer.json for components of specified type
     *
     * @param string $componentType
     * @param bool $asDataSet
     * @return array
     */
    public function get_composer_files($component_type, $as_data_set = true)
    {
        $key = __METHOD__ . '|' . implode('|', [$component_type, $as_data_set]);
        if (!isset(self::$_cache[$key])) {
            $excludes = $component_type == Component_Registrar::MODULE ? $this->get_module_test_dirs_regex() : [];
            $files = $this->get_files_subset($this->component_registrar->get_paths($component_type), 'composer.json', $excludes);
            self::$_cache[$key] = $files;
        }
        if ($as_data_set) {
            return self::compose_data_sets(self::$_cache[$key]);
        }
        return self::$_cache[$key];
    }
    /**
     * Read all text files by specified glob pattern and combine them into an array of valid files/directories
     *
     * The Magento root path is prepended to all (non-empty) entries
     *
     * @param string $globPattern
     * @return array
     * @throws \Exception if any of the patterns don't return any result
     */
    public function read_lists($glob_pattern)
    {
        $patterns = [];
        foreach (glob($glob_pattern) as $list) {
            $patterns = array_merge($patterns, file($list, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        }
        // Expand glob patterns
        $result = [];
        $incorrect_patterns = [];
        foreach ($patterns as $pattern) {
            $pattern = $pattern ?? '';
            if (0 === strpos($pattern, '#')) {
                continue;
            }
            $pattern_parts = explode(' ', $pattern);
            if (count($pattern_parts) == 3) {
                list($component_type, $component_name, $path_pattern) = $pattern_parts;
                $files = $this->get_path_by_component_pattern($component_type, $component_name, $path_pattern);
            } elseif (count($pattern_parts) == 1) {
                /**
                 * Note that glob() for directories will be returned as is,
                 * but passing directory is supported by the tools (phpcpd, phpmd, phpcs)
                 */
                $files = Glob::glob(BP . '/' . $pattern, Glob::GLOB_BRACE);
            } else {
                throw new \UnexpectedValueException("Incorrect pattern record '{$pattern}'. Supported formats: " . "'<componentType> <componentName> <glob_pattern>' or '<glob_pattern>'");
            }
            if (empty($files)) {
                $incorrect_patterns[] = $pattern;
            }
            $result = array_merge($result, $files);
        }
        if (!empty($incorrect_patterns)) {
            throw new Localized_Exception(__("The following patterns didn't return any result:\n%1", join("\n", $incorrect_patterns)));
        }
        return $result;
    }
    /**
     * Get paths by pattern for specified component component
     *
     * @param string $componentType
     * @param string $componentName
     * @param string $pathPattern
     * @return array
     */
    private function get_path_by_component_pattern($component_type, $component_name, $path_pattern)
    {
        $files = [];
        if ($component_type == '*') {
            $component_types = [Component_Registrar::MODULE, Component_Registrar::LIBRARY, Component_Registrar::THEME, Component_Registrar::LANGUAGE];
        } else {
            $component_types = [$component_type];
        }
        foreach ($component_types as $type) {
            if ($component_name == '*') {
                $files = array_merge($files, $this->dir_search->collect_files($type, $path_pattern));
            } else {
                $component_dir = $this->component_registrar->get_path($type, $component_name);
                if (!empty($component_dir)) {
                    $files = array_merge($files, Glob::glob($component_dir . '/' . $path_pattern, Glob::GLOB_BRACE));
                }
            }
        }
        return $files;
    }
    /**
     * Check module existence
     *
     * @param string $moduleName
     * @return bool
     */
    public function is_module_exists($module_name)
    {
        $key = __METHOD__ . "/{$module_name}";
        if (!isset(self::$_cache[$key])) {
            $component_path = $this->component_registrar->get_path(Component_Registrar::MODULE, $module_name);
            self::$_cache[$key] = $component_path && file_exists($component_path);
        }
        return self::$_cache[$key];
    }
    /**
     * Returns list of files in a given directory, minus files in specifically excluded directories.
     *
     * @param array $dirPatterns Directories to search in
     * @param string $fileNamePattern Pattern for filename
     * @param string|array $excludes Subdirectories to exlude, represented as regex
     * @return array Files in $dirPatterns but not in $excludes
     */
    protected function get_files_subset(array $dir_patterns, $file_name_pattern, $excludes)
    {
        if (!is_array($excludes)) {
            $excludes = [$excludes];
        }
        $file_set = self::get_files($dir_patterns, $file_name_pattern);
        foreach ($excludes as $exclude_regex) {
            $file_set = preg_grep($exclude_regex, $file_set, PREG_GREP_INVERT);
        }
        return $file_set;
    }
    /**
     * Get list of PHP files in setup application
     *
     * @param int $flags
     * @return array
     */
    private function get_setup_php_files($flags = null)
    {
        $files = [];
        $setup_app_path = BP . '/setup';
        if ($flags & self::INCLUDE_SETUP && file_exists($setup_app_path)) {
            $regex_iterator = $this->regex_iterator_factory->create($setup_app_path, '/.*php$/');
            foreach ($regex_iterator as $file) {
                $files[] = $file[0];
            }
        }
        return $files;
    }
}