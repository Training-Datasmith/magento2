<?php

declare (strict_types=1);
/**
 * An autoloader that uses class map
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Autoload;

class Class_Map
{
    /**
     * Absolute path to base directory that will be prepended as prefix to the included files
     *
     * @var string
     */
    protected $_base_dir;
    /**
     * Map of class name to file (relative to the base directory)
     *
     * array(
     *     'Class_Name' => 'relative/path/to/Class/Name.php',
     * )
     *
     * @var array
     */
    protected $_map = [];
    /**
     * Set base directory absolute path
     *
     * @param string $baseDir
     * @throws \InvalidArgumentException
     */
    public function __construct($base_dir)
    {
        $this->_base_dir = realpath($base_dir);
        if (!$this->_base_dir || !is_dir($this->_base_dir)) {
            throw new \InvalidArgumentException("Specified path is not a valid directory: '{$base_dir}'");
        }
    }
    /**
     * Find an absolute path to a file to be included
     *
     * @param string $class
     * @return string|bool
     */
    public function get_file($class)
    {
        if (isset($this->_map[$class])) {
            return $this->_base_dir . '/' . $this->_map[$class];
        }
        return false;
    }
    /**
     * Add classes files declaration to the map. New map will override existing values if such was defined before.
     *
     * @param array $map
     * @return $this
     */
    public function add_map(array $map)
    {
        $this->_map = array_merge($this->_map, $map);
        return $this;
    }
    /**
     * Resolve a class file and include it
     *
     * @param string $class
     * @return void
     */
    public function load($class)
    {
        $file = $this->get_file($class);
        if (file_exists($file)) {
            include $file;
        }
    }
}