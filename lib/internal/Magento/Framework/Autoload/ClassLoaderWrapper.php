<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Autoload;

use Composer\Autoload\Class_Loader;
/**
 * Wrapper designed to insulate the autoloader class provided by Composer
 */
class Class_Loader_Wrapper implements Autoloader_Interface
{
    /**
     * Using the autoloader class provided by Composer
     *
     * @var ClassLoader
     */
    protected $autoloader;
    /**
     * @param ClassLoader $autoloader
     */
    public function __construct(Class_Loader $autoloader)
    {
        $this->autoloader = $autoloader;
    }
    /**
     * @inheritdoc
     */
    public function add_psr4($ns_prefix, $paths, $prepend = false)
    {
        $this->autoloader->add_psr4($ns_prefix, $paths, $prepend);
    }
    /**
     * @inheritdoc
     */
    public function add_psr0($ns_prefix, $paths, $prepend = false)
    {
        $this->autoloader->add($ns_prefix, $paths, $prepend);
    }
    /**
     * @inheritdoc
     */
    public function set_psr0($ns_prefix, $paths)
    {
        $this->autoloader->set($ns_prefix, $paths);
    }
    /**
     * @inheritdoc
     */
    public function set_psr4($ns_prefix, $paths)
    {
        $this->autoloader->set_psr4($ns_prefix, $paths);
    }
    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function load_class($class_name)
    {
        return $this->autoloader->load_class($class_name) === true;
    }
    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function find_file($class_name)
    {
        /**
         * Composer remembers that files don't exist even after they are generated. This clears the entry for
         * $className so we can check the filesystem again for class existence.
         */
        if ($class_name && $class_name[0] === '\\') {
            $class_name = substr($class_name, 1);
        }
        $this->autoloader->add_class_map([$class_name => null]);
        return $this->autoloader->find_file($class_name);
    }
}