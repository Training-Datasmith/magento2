<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Filesystem\Driver_Pool;
use Magento\Framework\Filesystem\File\Read_Factory;
/**
 * @api
 * @since 100.0.2
 */
class File_Iterator implements \Iterator, \Countable
{
    /**
     * @var array
     */
    protected $paths = [];
    /**
     * @var int
     */
    protected $position;
    /**
     * @var ReadFactory
     */
    protected $file_read_factory;
    /**
     * Constructor
     *
     * @param ReadFactory $readFactory
     * @param array $paths
     */
    public function __construct(Read_Factory $read_factory, array $paths)
    {
        $this->file_read_factory = $read_factory;
        $this->paths = $paths;
        $this->position = 0;
    }
    /**
     * Rewind
     *
     * @return void
     */
    #[\Return_Type_Will_Change]
    public function rewind()
    {
        reset($this->paths);
    }
    /**
     * Current
     *
     * @return string
     */
    #[\Return_Type_Will_Change]
    public function current()
    {
        $file_read = $this->file_read_factory->create($this->key(), Driver_Pool::FILE);
        return $file_read->read_all();
    }
    /**
     * Key
     *
     * @return mixed
     */
    #[\Return_Type_Will_Change]
    public function key()
    {
        return current($this->paths);
    }
    /**
     * Next
     *
     * @return void
     */
    #[\Return_Type_Will_Change]
    public function next()
    {
        next($this->paths);
    }
    /**
     * Valid
     *
     * @return bool
     */
    #[\Return_Type_Will_Change]
    public function valid()
    {
        return (bool) $this->key();
    }
    /**
     * Convert to an array
     *
     * @return array
     */
    #[\Return_Type_Will_Change]
    public function to_array()
    {
        $result = [];
        foreach ($this as $item) {
            $result[$this->key()] = $item;
        }
        return $result;
    }
    /**
     * Count
     *
     * @return int
     */
    #[\Return_Type_Will_Change]
    public function count()
    {
        return count($this->paths);
    }
}