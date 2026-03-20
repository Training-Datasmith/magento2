<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Language;

use Magento\Framework\Component\Component_Registrar;
use Magento\Framework\Filesystem\Directory\Read_Factory;
/**
 * A service for reading language package dictionaries
 *
 * @api
 * @since 100.0.2
 */
class Dictionary
{
    /**
     * Paths of all language packages
     *
     * @var string[]
     */
    private $paths;
    /**
     * Creates directory read objects
     *
     * @var ReadFactory
     */
    private $directory_read_factory;
    /**
     * Component Registrar
     *
     * @var ReadFactory
     */
    private $component_registrar;
    /**
     * @var ConfigFactory
     */
    private $config_factory;
    /**
     * @var array
     */
    private $pack_list = [];
    /**
     * @param ReadFactory $directoryReadFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param ConfigFactory $configFactory
     */
    public function __construct(Read_Factory $directory_read_factory, Component_Registrar $component_registrar, Config_Factory $config_factory)
    {
        $this->directory_read_factory = $directory_read_factory;
        $this->component_registrar = $component_registrar;
        $this->config_factory = $config_factory;
    }
    /**
     * Load and merge all phrases from language packs by specified code
     *
     * Takes into account inheritance between language packs
     * Returns associative array where key is phrase in the source code and value is its translation
     *
     * @param string $languageCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get_dictionary($language_code)
    {
        $languages = [];
        $this->paths = $this->component_registrar->get_paths(Component_Registrar::LANGUAGE);
        foreach ($this->paths as $path) {
            $directory_read = $this->directory_read_factory->create($path);
            if ($directory_read->is_exist('language.xml')) {
                $xml_source = $directory_read->read_file('language.xml');
                try {
                    $language_config = $this->config_factory->create(['source' => $xml_source]);
                } catch (\Magento\Framework\Config\Dom\Validation_Exception $e) {
                    throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('The XML in file "%1" is invalid:' . "\n%2\nVerify the XML and try again.", [$path . '/language.xml', $e->get_message()]), $e);
                }
                $this->pack_list[$language_config->get_vendor()][$language_config->get_package()] = $language_config;
                if ($language_config->get_code() === $language_code) {
                    $languages[] = $language_config;
                }
            }
        }
        // Collect the inherited packages with meta-information of sorting
        $packs = [];
        foreach ($languages as $language_config) {
            $this->collect_inherited_packs($language_config, $packs);
        }
        // Get sorted packs
        $packs = $this->get_sorted_packs($packs);
        // Merge all packages of translation to one dictionary
        $result = [];
        foreach ($packs as $pack_info) {
            /** @var Config $languageConfig */
            $language_config = $pack_info['language'];
            $dictionary = $this->read_pack_csv($language_config->get_vendor(), $language_config->get_package());
            foreach ($dictionary as $key => $value) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    /**
     * Get sorted packs
     *
     * First level packs (inheritance_level eq 0) sort by 'sort order' (ascending)
     * Inherited packs has the same order as declared in parent config (language.xml)
     *
     * @param array $allPacks
     *
     * @return array
     */
    private function get_sorted_packs($all_packs)
    {
        // Get first level (inheritance_level) packs and sort by provided sort order (descending)
        $first_level_packs = array_filter($all_packs, function ($pack) {
            return $pack['inheritance_level'] === 0;
        });
        uasort($first_level_packs, [$this, 'sortPacks']);
        // Add inherited packs
        $sorted_packs = [];
        foreach ($first_level_packs as $pack) {
            $this->add_inherited_packs($all_packs, $pack, $sorted_packs);
        }
        // Reverse array: the first element has the lowest priority, the last one - the highest
        return array_reverse($sorted_packs, true);
    }
    /**
     * Line up (flatten) a tree of inheritance of language packs
     *
     * Record level of recursion (level of inheritance) for further use in sorting
     *
     * @param Config $languageConfig
     * @param array $result
     * @param int $level
     * @param array $visitedPacks
     * @return void
     */
    private function collect_inherited_packs($language_config, &$result, $level = 0, array &$visited_packs = [])
    {
        $pack_key = implode('|', [$language_config->get_vendor(), $language_config->get_package()]);
        if (!isset($visited_packs[$pack_key]) && (!isset($result[$pack_key]) || $result[$pack_key]['inheritance_level'] < $level)) {
            $visited_packs[$pack_key] = true;
            $result[$pack_key] = ['inheritance_level' => $level, 'sort_order' => $language_config->get_sort_order(), 'language' => $language_config, 'key' => $pack_key];
            foreach ($language_config->get_uses() as $reuse) {
                if (isset($this->pack_list[$reuse['vendor']][$reuse['package']])) {
                    $parent_language_config = $this->pack_list[$reuse['vendor']][$reuse['package']];
                    $this->collect_inherited_packs($parent_language_config, $result, $level + 1, $visited_packs);
                }
            }
        }
    }
    /**
     * Add inherited packs to sorted packs
     *
     * @param array $packs
     * @param array $pack
     * @param array $sortedPacks
     *
     * @return void
     */
    private function add_inherited_packs($packs, $pack, &$sorted_packs)
    {
        if (isset($sorted_packs[$pack['key']])) {
            return;
        }
        $sorted_packs[$pack['key']] = $pack;
        foreach ($pack['language']->get_uses() as $reuse) {
            $pack_key = implode('|', [$reuse['vendor'], $reuse['package']]);
            if (isset($packs[$pack_key])) {
                $this->add_inherited_packs($packs, $packs[$pack_key], $sorted_packs);
            }
        }
    }
    /**
     * Sub-routine for custom sorting packs using sort order (descending)
     *
     * @param array $current
     * @param array $next
     *
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function sort_packs($current, $next)
    {
        if ($current['sort_order'] > $next['sort_order']) {
            return -1;
        } elseif ($current['sort_order'] < $next['sort_order']) {
            return 1;
        }
        return strcmp($next['key'], $current['key']);
    }
    /**
     * Read the CSV-files in a language package
     *
     * The files are sorted alphabetically, then each of them is read, and results are recorded into key => value array
     *
     * @param string $vendor
     * @param string $package
     * @return array
     */
    private function read_pack_csv($vendor, $package)
    {
        $path = $this->component_registrar->get_path(Component_Registrar::LANGUAGE, strtolower($vendor . '_' . $package));
        $result = [];
        if (isset($path)) {
            $directory_read = $this->directory_read_factory->create($path);
            $found_csv_files = $directory_read->search('*.csv');
            foreach ($found_csv_files as $found_csv_file) {
                $file = $directory_read->open_file($found_csv_file);
                while (($row = $file->read_csv()) !== false) {
                    if (is_array($row) && count($row) > 1) {
                        $result[$row[0]] = $row[1];
                    }
                }
            }
        }
        return $result;
    }
}