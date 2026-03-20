<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Composer;

use Composer\Package\Complete_Package_Interface;
use Composer\Package\Link;
use Magento\Framework\App\Object_Manager;
/**
 * Class ComposerInformation uses Composer to determine dependency information.
 */
class Composer_Information
{
    /**
     * Magento2 theme type
     */
    public const THEME_PACKAGE_TYPE = 'magento2-theme';
    /**
     * Magento2 module type
     */
    public const MODULE_PACKAGE_TYPE = 'magento2-module';
    /**
     * Magento2 language type
     */
    public const LANGUAGE_PACKAGE_TYPE = 'magento2-language';
    /**
     * Magento2 metapackage type
     */
    public const METAPACKAGE_PACKAGE_TYPE = 'metapackage';
    /**
     * Magento2 library type
     */
    public const LIBRARY_PACKAGE_TYPE = 'magento2-library';
    /**
     * Magento2 component type
     */
    public const COMPONENT_PACKAGE_TYPE = 'magento2-component';
    /**
     * Default composer repository key
     */
    public const COMPOSER_DEFAULT_REPO_KEY = 'packagist.org';
    /**#@+
     * Composer command
     */
    public const COMPOSER_SHOW = 'show';
    /**#@-*/
    /**#@+
     * Composer command params and options
     */
    public const PARAM_COMMAND = 'command';
    public const PARAM_PACKAGE = 'package';
    public const PARAM_AVAILABLE = '--available';
    /**#@-*/
    /**#@-*/
    private $composer;
    /**
     * @var \Composer\Package\Locker
     */
    private $locker;
    /**
     * @var array
     */
    private static $package_types = [self::THEME_PACKAGE_TYPE, self::LANGUAGE_PACKAGE_TYPE, self::MODULE_PACKAGE_TYPE, self::LIBRARY_PACKAGE_TYPE, self::COMPONENT_PACKAGE_TYPE, self::METAPACKAGE_PACKAGE_TYPE];
    /**
     * @var ComposerFactory
     */
    private $composer_factory;
    /**
     * @param ComposerFactory $composerFactory
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Composer_Factory $composer_factory)
    {
        $this->composer_factory = $composer_factory;
    }
    /**
     * Retrieves required php version
     *
     * @return string
     * @throws \Exception If attributes are missing in composer.lock file.
     */
    public function get_required_php_version()
    {
        if ($this->is_magento_root()) {
            $all_platform_reqs = $this->get_locker()->get_platform_requirements(true);
            $required_php_version = $all_platform_reqs['php']->get_pretty_constraint();
        } else {
            $packages = $this->get_locker()->get_locked_repository()->get_packages();
            /** @var CompletePackageInterface $package */
            foreach ($packages as $package) {
                if ($package instanceof Complete_Package_Interface) {
                    $package_name = $package->get_pretty_name();
                    if ($package_name === 'magento/product-community-edition') {
                        $php_requirement_link = $package->get_requires()['php'];
                        if ($php_requirement_link instanceof Link) {
                            $required_php_version = $php_requirement_link->get_pretty_constraint();
                        }
                    }
                }
            }
        }
        if (!isset($required_php_version)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Cannot find php version requirement in \'composer.lock\' file');
        }
        return $required_php_version;
    }
    /**
     * Retrieve list of required extensions
     *
     * Collect required extensions from composer.lock file
     *
     * @return array
     * @throws \Exception If attributes are missing in composer.lock file.
     */
    public function get_required_extensions()
    {
        $required_extensions = [];
        $all_platform_reqs = [array_keys($this->get_locker()->get_platform_requirements(true))];
        if (!$this->is_magento_root()) {
            /** @var CompletePackageInterface $package */
            foreach ($this->get_locker()->get_locked_repository()->get_packages() as $package) {
                $requires = array_keys($package->get_requires());
                $all_platform_reqs[] = $requires;
            }
        }
        $all_platform_reqs = array_merge([], ...$all_platform_reqs);
        foreach ($all_platform_reqs as $req_index) {
            if (substr($req_index, 0, 4) === 'ext-') {
                $required_extensions[] = substr($req_index, 4);
            }
        }
        return array_unique($required_extensions);
    }
    /**
     * Retrieve list of suggested extensions
     *
     * Collect suggests from composer.lock file and modules composer.json files
     *
     * @return array
     */
    public function get_suggested_packages()
    {
        $suggests = [];
        /** @var \Composer\Package\CompletePackage $package */
        foreach ($this->get_locker()->get_locked_repository()->get_packages() as $package) {
            $suggests += $package->get_suggests();
        }
        return array_unique($suggests);
    }
    /**
     * Collect required packages from root composer.lock file
     *
     * @return array
     */
    public function get_root_required_packages()
    {
        $packages = [];
        /** @var CompletePackageInterface $package */
        foreach ($this->get_locker()->get_locked_repository()->get_packages() as $package) {
            $packages[] = $package->get_name();
        }
        return $packages;
    }
    /**
     * Collect required packages and types from root composer.lock file
     *
     * @return array
     */
    public function get_root_required_package_types_by_name()
    {
        $packages = [];
        /** @var CompletePackageInterface $package */
        foreach ($this->get_locker()->get_locked_repository()->get_packages() as $package) {
            $packages[$package->get_name()] = $package->get_type();
        }
        return $packages;
    }
    /**
     * Collect all installed Magento packages from composer.lock
     *
     * @return array
     */
    public function get_installed_magento_packages()
    {
        $packages = [];
        /** @var CompletePackageInterface $package */
        foreach ($this->get_locker()->get_locked_repository()->get_packages() as $package) {
            if (in_array($package->get_type(), self::$package_types) && !$this->is_system_package($package->get_pretty_name())) {
                $packages[$package->get_name()] = ['name' => $package->get_name(), 'type' => $package->get_type(), 'version' => $package->get_pretty_version()];
            }
        }
        return $packages;
    }
    /**
     * Collect all system packages from composer.lock
     *
     * @return array
     */
    public function get_system_packages()
    {
        $packages = [];
        /** @var CompletePackageInterface $package */
        foreach ($this->get_locker()->get_locked_repository()->get_packages() as $package) {
            if ($this->is_system_package($package->get_name())) {
                $packages[$package->get_name()] = ['name' => $package->get_name(), 'type' => $package->get_type(), 'version' => $package->get_pretty_version()];
            }
        }
        return $packages;
    }
    /**
     * Checks if the passed packaged is system package
     *
     * @param string $packageName
     * @return bool
     */
    public function is_system_package($package_name = '')
    {
        if (preg_match('/magento\/product-.*?-edition/', $package_name) == 1) {
            return true;
        }
        return false;
    }
    /**
     * Determines if Magento is the root package or it is included as a requirement.
     *
     * @return boolean
     */
    public function is_magento_root()
    {
        $root_package = $this->get_composer()->get_package();
        return (bool) preg_match('/magento\/magento2...?/', $root_package->get_name());
    }
    /**
     * Get root package
     *
     * @return \Composer\Package\RootPackageInterface
     */
    public function get_root_package()
    {
        return $this->get_composer()->get_package();
    }
    /**
     * Check if a package is inside the root composer or not
     *
     * @param string $packageName
     * @return bool
     */
    public function is_package_in_composer_json($package_name)
    {
        return in_array($package_name, array_keys($this->get_composer()->get_package()->get_requires())) || in_array($package_name, array_keys($this->get_composer()->get_package()->get_dev_requires()));
    }
    /**
     * Retrieve magento packages types.
     *
     * @return array
     */
    public function get_packages_types()
    {
        return self::$package_types;
    }
    /**
     * Retrieve magento packages requirements.
     *
     * @param string $name
     * @param string $version
     * @return array
     */
    public function get_package_requirements($name, $version)
    {
        $package = $this->get_composer()->get_repository_manager()->find_package($name, $version);
        return $package->get_requires();
    }
    /**
     * Returns all repository URLs, except local and packagists.
     *
     * @return string[]
     */
    public function get_root_repositories()
    {
        $repository_urls = [];
        foreach ($this->get_composer()->get_config()->get_repositories() as $key => $repository) {
            if ($key !== self::COMPOSER_DEFAULT_REPO_KEY) {
                $repository_urls[] = $repository['url'];
            }
        }
        return $repository_urls;
    }
    /**
     * Load composerFactory
     *
     * @return ComposerFactory
     * @deprecated 100.1.0
     */
    private function get_composer_factory()
    {
        if (!$this->composer_factory) {
            $this->composer_factory = Object_Manager::get_instance()->get(Composer_Factory::class);
        }
        return $this->composer_factory;
    }
    /**
     * Load composer
     *
     * @return \Composer\Composer
     */
    private function get_composer()
    {
        if (!$this->composer) {
            $this->composer = $this->get_composer_factory()->create();
        }
        return $this->composer;
    }
    /**
     * Load locker
     *
     * @return \Composer\Package\Locker
     */
    private function get_locker()
    {
        if (!$this->locker) {
            $this->locker = $this->get_composer()->get_locker();
        }
        return $this->locker;
    }
}