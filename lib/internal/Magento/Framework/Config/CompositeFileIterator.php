<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Config;

use Magento\Framework\Filesystem\File\Read_Factory;
/**
 * Combine existing file iterator and new files.
 */
class Composite_File_Iterator extends File_Iterator
{
    /**
     * @var FileIterator
     */
    private $existing_iterator;
    /**
     * @param ReadFactory $readFactory
     * @param array $paths
     * @param FileIterator $existingIterator
     */
    public function __construct(Read_Factory $read_factory, array $paths, File_Iterator $existing_iterator)
    {
        parent::__construct($read_factory, $paths);
        $this->existing_iterator = $existing_iterator;
    }
    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->existing_iterator->rewind();
        parent::rewind();
    }
    /**
     * @inheritDoc
     */
    public function current()
    {
        if ($this->existing_iterator->valid()) {
            return $this->existing_iterator->current();
        }
        return parent::current();
    }
    /**
     * @inheritDoc
     */
    public function key()
    {
        if ($this->existing_iterator->valid()) {
            return $this->existing_iterator->key();
        }
        return parent::key();
    }
    /**
     * @inheritDoc
     */
    public function next()
    {
        if ($this->existing_iterator->valid()) {
            $this->existing_iterator->next();
        } else {
            parent::next();
        }
    }
    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->existing_iterator->valid() || parent::valid();
    }
    /**
     * @inheritDoc
     */
    public function to_array()
    {
        return array_merge($this->existing_iterator->to_array(), parent::to_array());
    }
    /**
     * @inheritDoc
     */
    public function count()
    {
        return $this->existing_iterator->count() + parent::count();
    }
}