<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache\Frontend\Adapter;

/**
 * Adapter for Magento -> Zend cache frontend interfaces
 *
 * @deprecated No longer used in production. All cache operations now use Symfony cache adapter.
 * @see \Magento\Framework\Cache\Frontend\Adapter\Symfony
 */
class Zend implements \Magento\Framework\Cache\Frontend_Interface
{
    /**
     * @var \Zend_Cache_Core
     */
    protected $_frontend;
    /**
     * Factory that creates the \Zend_Cache_Cores
     *
     * @var \Closure
     */
    private $frontend_factory;
    /**
     * The pid that owns the $_frontend object
     *
     * @var int
     */
    private $pid;
    /**
     * We need to keep references to parent's frontends so that they don't get destroyed
     *
     * @var array
     */
    private $parent_frontends = [];
    /**
     * @param \Closure $frontendFactory
     */
    public function __construct(\Closure $frontend_factory)
    {
        $this->frontend_factory = $frontend_factory;
        $this->_frontend = $frontend_factory();
        $this->pid = getmypid();
    }
    /**
     * @inheritdoc
     */
    public function test($identifier)
    {
        return $this->get_front_end()->test($this->_unify_id($identifier));
    }
    /**
     * @inheritdoc
     */
    public function load($identifier)
    {
        return $this->get_front_end()->load($this->_unify_id($identifier));
    }
    /**
     * @inheritdoc
     */
    public function save($data, $identifier, array $tags = [], $life_time = null)
    {
        return $this->get_front_end()->save($data, $this->_unify_id($identifier), $this->_unify_ids($tags), $life_time);
    }
    /**
     * @inheritdoc
     */
    public function remove($identifier)
    {
        return $this->get_front_end()->remove($this->_unify_id($identifier));
    }
    /**
     * @inheritdoc
     *
     * @throws \InvalidArgumentException Exception is thrown when non-supported cleaning mode is specified
     * @throws \Zend_Cache_Exception
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        // Cleaning modes 'old' and 'notMatchingTag' are prohibited as a trade off for decoration reliability
        if (!in_array($mode, [\Zend_Cache::CLEANING_MODE_ALL, \Zend_Cache::CLEANING_MODE_MATCHING_TAG, \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG])) {
            throw new \InvalidArgumentException("Magento cache frontend does not support the cleaning mode '{$mode}'.");
        }
        return $this->get_front_end()->clean($mode, $this->_unify_ids($tags));
    }
    /**
     * @inheritdoc
     */
    public function get_backend()
    {
        return $this->get_front_end()->get_backend();
    }
    /**
     * @inheritdoc
     */
    public function get_low_level_frontend()
    {
        return $this->get_front_end();
    }
    /**
     * Retrieve single unified identifier
     *
     * @param string $identifier
     * @return string
     */
    protected function _unify_id($identifier)
    {
        return strtoupper($identifier);
    }
    /**
     * Retrieve multiple unified identifiers
     *
     * @param array $ids
     * @return array
     */
    protected function _unify_ids(array $ids)
    {
        foreach ($ids as $key => $value) {
            $ids[$key] = $this->_unify_id($value);
        }
        return $ids;
    }
    /**
     * Get frontEnd cache adapter for current pid
     *
     * @return \Zend_Cache_Core
     */
    private function get_front_end()
    {
        if (getmypid() === $this->pid) {
            return $this->_frontend;
        }
        // Note: We hide the parent process's _frontend so that the destructor won't get called on it.
        // If the destructor were called, then the parent process's connection would be disconnected.
        $this->parent_frontends[] = $this->_frontend;
        $frontend_factory = $this->frontend_factory;
        $this->_frontend = $frontend_factory();
        $this->pid = getmypid();
        return $this->_frontend;
    }
}