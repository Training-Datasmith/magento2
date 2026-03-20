<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Autoload;

/**
 * Interface for an autoloader class that allows the dynamic modification of PSR-0 and PSR-4 mappings
 *
 * @api
 */
interface Autoloader_Interface
{
    /**
     * Adds a PSR-4 mapping from a namespace prefix to directories to search in for the corresponding class
     *
     * @param string $nsPrefix The namespace prefix of the PSR-4 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @param bool $prepend Whether to append the given path or paths to the paths already associated with the prefix
     * @return void
     */
    public function add_psr4($ns_prefix, $paths, $prepend = false);
    /**
     * Adds a PSR-0 mapping from a namespace prefix to directories to search in for the corresponding class
     *
     * @param string $nsPrefix The namespace prefix of the PSR-0 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @param bool $prepend Whether to append the given path or paths to the paths already associated with the prefix
     * @return void
     */
    public function add_psr0($ns_prefix, $paths, $prepend = false);
    /**
     * Creates new PSR-0 mappings from the given prefix to the given set of paths, eliminating previous mappings
     *
     * @param string $nsPrefix The namespace prefix of the PSR-0 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @return void
     */
    public function set_psr0($ns_prefix, $paths);
    /**
     * Creates new PSR-4 mappings from the given prefix to the given set of paths, eliminating previous mappings
     *
     * @param string $nsPrefix The namespace prefix of the PSR-0 mapping
     * @param string|array $paths The path or paths to look in for the given prefix
     * @return void
     */
    public function set_psr4($ns_prefix, $paths);
    /**
     * Attempts to load a class and returns true if successful.
     *
     * @param string $className
     * @return bool
     */
    public function load_class($class_name);
    /**
     * Get filepath of class on system or false if it does not exist
     *
     * @param string $className
     * @return string|bool
     */
    public function find_file($class_name);
}