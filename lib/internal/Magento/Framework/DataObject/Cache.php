<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data_Object;

/**
 * Object Cache
 *
 * Stores objects for reuse, cleanup and to avoid circular references
 */
class Cache
{
    /**
     * Singleton instance
     *
     * @var \Magento\Framework\DataObject\Cache
     */
    protected static $_instance;
    /**
     * Running object index for anonymous objects
     *
     * @var integer
     */
    protected $_idx = 0;
    /**
     * Array of objects
     *
     * @var array of objects
     */
    protected $_objects = [];
    /**
     * SPL object hashes
     *
     * @var array
     */
    protected $_hashes = [];
    /**
     * SPL hashes by object
     *
     * @var array
     */
    protected $_object_hashes = [];
    /**
     * Objects by tags for cleanup
     *
     * @var array 2D
     */
    protected $_tags = [];
    /**
     * Tags by objects
     *
     * @var array 2D
     */
    protected $_object_tags = [];
    /**
     * References to objects
     *
     * @var array
     */
    protected $_references = [];
    /**
     * References by object
     *
     * @var array 2D
     */
    protected $_object_references = [];
    /**
     * Debug data such as backtrace per class
     *
     * @var array
     */
    protected $_debug = [];
    /**
     * Singleton factory
     *
     * @return \Magento\Framework\DataObject\Cache
     */
    public static function singleton()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * Load an object from registry
     *
     * @param string|object $idx
     * @param object $default
     *
     * @return object
     */
    public function load($idx, $default = null)
    {
        if (isset($this->_references[$idx])) {
            $idx = $this->_references[$idx];
        }
        if (isset($this->_objects[$idx])) {
            return $this->_objects[$idx];
        }
        return $default;
    }
    /**
     * Save an object entry
     *
     * @param object $object
     * @param string $idx
     * @param array|string $tags
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save($object, $idx = null, $tags = null)
    {
        if (!is_object($object)) {
            return false;
        }
        $hash = spl_object_hash($object);
        if ($idx !== null && strpos($idx, '{') !== false) {
            $idx = str_replace('{hash}', $hash, $idx);
        }
        if (isset($this->_hashes[$hash])) {
            if ($idx !== null) {
                $this->_references[$idx] = $this->_hashes[$hash];
            }
            return $this->_hashes[$hash];
        }
        if ($idx === null) {
            $idx = '#' . ++$this->_idx;
        }
        if (isset($this->_objects[$idx])) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Object already exists in registry (%1). Old object class: %2, new object class: %3', [$idx, get_class($this->_objects[$idx]), get_class($object)]));
        }
        $this->_objects[$idx] = $object;
        $this->_hashes[$hash] = $idx;
        $this->_object_hashes[$idx] = $hash;
        if (is_string($tags)) {
            $this->_tags[$tags][$idx] = true;
            $this->_object_tags[$idx][$tags] = true;
        } elseif (is_array($tags)) {
            foreach ($tags as $t) {
                $this->_tags[$t][$idx] = true;
                $this->_object_tags[$idx][$t] = true;
            }
        }
        return $idx;
    }
    /**
     * Add a reference to an object
     *
     * @param string|array $refName
     * @param string $idx
     *
     * @return bool|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reference($ref_name, $idx)
    {
        if (is_array($ref_name)) {
            foreach ($ref_name as $ref) {
                $this->reference($ref, $idx);
            }
            return;
        }
        if (isset($this->_references[$ref_name])) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('The reference already exists: %1. New index: %2, old index: %3', [$ref_name, $idx, $this->_references[$ref_name]]));
        }
        $this->_references[$ref_name] = $idx;
        $this->_object_references[$idx][$ref_name] = true;
        return true;
    }
    /**
     * Delete an object from registry
     *
     * @param string|object $idx
     *
     * @return boolean
     */
    public function delete($idx)
    {
        if (is_object($idx)) {
            $idx = $this->find($idx);
            if (false === $idx) {
                return false;
            }
            unset($this->_objects[$idx]);
            return false;
        } elseif (!isset($this->_objects[$idx])) {
            return false;
        }
        unset($this->_objects[$idx]);
        unset($this->_hashes[$this->_object_hashes[$idx]], $this->_object_hashes[$idx]);
        if (isset($this->_object_tags[$idx])) {
            foreach ($this->_object_tags[$idx] as $t => $dummy) {
                unset($this->_tags[$t][$idx]);
            }
            unset($this->_object_tags[$idx]);
        }
        if (isset($this->_object_references[$idx])) {
            foreach ($this->_references as $r => $dummy) {
                unset($this->_references[$r]);
            }
            unset($this->_object_references[$idx]);
        }
        return true;
    }
    /**
     * Cleanup by class name for objects of subclasses too
     *
     * @param string $class
     *
     * @return void
     */
    public function delete_by_class($class)
    {
        foreach ($this->_objects as $idx => $object) {
            if ($object instanceof $class) {
                $this->delete($idx);
            }
        }
    }
    /**
     * Cleanup objects by tags
     *
     * @param array|string $tags
     *
     * @return true
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function delete_by_tags($tags)
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }
        foreach ($tags as $t) {
            foreach ($this->_tags[$t] as $idx => $dummy) {
                $this->delete($idx);
            }
        }
        return true;
    }
    /**
     * Check whether object id exists in registry
     *
     * @param string $idx
     *
     * @return boolean
     */
    public function has($idx)
    {
        return isset($this->_objects[$idx]) || isset($this->_references[$idx]);
    }
    /**
     * Find an object id
     *
     * @param object $object
     *
     * @return string|boolean
     */
    public function find($object)
    {
        foreach ($this->_objects as $idx => $obj) {
            if ($object === $obj) {
                return $idx;
            }
        }
        return false;
    }
    /**
     * Find objects by ids
     *
     * @param string[] $ids
     *
     * @return array
     */
    public function find_by_ids($ids)
    {
        $objects = [];
        foreach ($this->_objects as $idx => $obj) {
            if (in_array($idx, $ids)) {
                $objects[$idx] = $obj;
            }
        }
        return $objects;
    }
    /**
     * Find object by hash
     *
     * @param string $hash
     *
     * @return object
     */
    public function find_by_hash($hash)
    {
        return isset($this->_hashes[$hash]) ? $this->_objects[$this->_hashes[$hash]] : null;
    }
    /**
     * Find objects by tags
     *
     * @param array|string $tags
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function find_by_tags($tags)
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }
        $objects = [];
        foreach ($tags as $t) {
            foreach ($this->_tags[$t] as $idx => $dummy) {
                if (isset($objects[$idx])) {
                    continue;
                }
                $objects[$idx] = $this->load($idx);
            }
        }
        return $objects;
    }
    /**
     * Find by class name for objects of subclasses too
     *
     * @param string $class
     *
     * @return array
     */
    public function find_by_class($class)
    {
        $objects = [];
        foreach ($this->_objects as $idx => $object) {
            if ($object instanceof $class) {
                $objects[$idx] = $object;
            }
        }
        return $objects;
    }
    /**
     * Debug
     *
     * @param string $idx
     * @param object|null $object
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function debug($idx, $object = null)
    {
        $bt = debug_backtrace();
        $debug = [];
        foreach ($bt as $i => $step) {
            $debug[$i] = ['file' => isset($step['file']) ? $step['file'] : null, 'line' => isset($step['line']) ? $step['line'] : null, 'function' => isset($step['function']) ? $step['function'] : null];
        }
        $this->_debug[$idx] = $debug;
    }
    /**
     * Return debug information by ids
     *
     * @param array|string $ids
     *
     * @return array
     */
    public function debug_by_ids($ids)
    {
        if (is_string($ids)) {
            $ids = [$ids];
        }
        $debug = [];
        foreach ($ids as $idx) {
            $debug[$idx] = $this->_debug[$idx];
        }
        return $debug;
    }
    /**
     * Get all objects
     *
     * @return array
     */
    public function get_all_objects()
    {
        return $this->_objects;
    }
    /**
     * Get all tags
     *
     * @return array
     */
    public function get_all_tags()
    {
        return $this->_tags;
    }
    /**
     * Get all tags by object
     *
     * @return array
     */
    public function get_all_tags_by_object()
    {
        return $this->_object_tags;
    }
    /**
     * Get all references
     *
     * @return array
     */
    public function get_all_references()
    {
        return $this->_references;
    }
}