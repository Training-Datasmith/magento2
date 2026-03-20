<?php

declare (strict_types=1);
/**
 * Magento application product metadata
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Composer\Composer_Factory;
use Magento\Framework\Composer\Composer_Information;
use Magento\Framework\Composer\Composer_Json_Finder;
/**
 * Magento application product metadata
 */
class Product_Metadata implements Product_Metadata_Interface
{
    /**
     * Magento product edition
     */
    public const EDITION_NAME = 'Community';
    /**
     * Magento product name
     */
    public const PRODUCT_NAME = 'Magento';
    /**
     * Magento version cache key
     */
    public const VERSION_CACHE_KEY = 'mage-version';
    /**
     * Product version
     *
     * @var string
     */
    protected $version;
    /**
     * @var \Magento\Framework\Composer\ComposerJsonFinder
     * @deprecated 100.1.0
     */
    protected $composer_json_finder;
    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     */
    private $composer_information;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * ProductMetadata constructor.
     * @param ComposerJsonFinder $composerJsonFinder
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(Composer_Json_Finder $composer_json_finder, ?Cache_Interface $cache = null)
    {
        $this->composer_json_finder = $composer_json_finder;
        $this->cache = $cache ?: Object_Manager::get_instance()->get(Cache_Interface::class);
    }
    /**
     * Get Product version
     *
     * @return string
     */
    public function get_version()
    {
        $this->version = $this->version ?: $this->cache->load(self::VERSION_CACHE_KEY);
        if (!$this->version) {
            if (!$this->version = $this->get_system_package_version()) {
                if ($this->get_composer_information()->is_magento_root()) {
                    $this->version = $this->get_composer_information()->get_root_package()->get_pretty_version();
                } else {
                    $this->version = 'UNKNOWN';
                }
            }
            $this->cache->save($this->version, self::VERSION_CACHE_KEY, [Config::CACHE_TAG]);
        }
        return $this->version;
    }
    /**
     * Get Product edition
     *
     * @return string
     */
    public function get_edition()
    {
        return self::EDITION_NAME;
    }
    /**
     * Get Product name
     *
     * @return string
     */
    public function get_name()
    {
        return self::PRODUCT_NAME;
    }
    /**
     * Get version from system package
     *
     * @return string
     * @deprecated 100.1.0
     */
    private function get_system_package_version()
    {
        $packages = $this->get_composer_information()->get_system_packages();
        foreach ($packages as $package) {
            if (isset($package['name']) && isset($package['version'])) {
                return $package['version'];
            }
        }
        return '';
    }
    /**
     * Load composerInformation
     *
     * @return ComposerInformation
     * @deprecated 100.1.0
     */
    private function get_composer_information()
    {
        if (!$this->composer_information) {
            $directory_list = new Directory_List(BP);
            $composer_factory = new Composer_Factory($directory_list, $this->composer_json_finder);
            $this->composer_information = new Composer_Information($composer_factory);
        }
        return $this->composer_information;
    }
}