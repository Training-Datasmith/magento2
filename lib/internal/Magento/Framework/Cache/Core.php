<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache;

use Magento\Framework\Cache\Backend\Redis;
use Zend_Cache;
use Zend_Cache_Exception;
/**
 * Extended Zend Cache Core with backend decorator support
 *
 * @deprecated No longer used in production. All cache operations now use Symfony cache adapter.
 * @see \Magento\Framework\Cache\Frontend\Adapter\Symfony
 */
class Core extends \Zend_Cache_Core
{
    /**
     * Available options
     *
     * ====> (array) backend_decorators :
     * - array of decorators to decorate cache backend. Each element of this array should contain:
     * -- 'class' - concrete decorator, descendant of \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator
     * -- 'options' - optional array of specific decorator options
     * @var array
     */
    protected $_specific_options = ['backend_decorators' => [], 'disable_save' => false];
    /**
     * Make and return a cache id
     *
     * Checks 'cache_id_prefix' and returns new id with prefix or simply the id if null
     *
     * @param  string $cacheId Cache id
     * @return string Cache id (with or without prefix)
     */
    protected function _id($cache_id)
    {
        if ($cache_id !== null) {
            $cache_id = str_replace('.', '__', $cache_id);
            //reduce collision chances
            $cache_id = preg_replace('/([^a-zA-Z0-9_]{1,1})/', '_', $cache_id);
            if (isset($this->_options['cache_id_prefix'])) {
                $cache_id = $this->_options['cache_id_prefix'] . $cache_id;
            }
        }
        return $cache_id;
    }
    /**
     * Prepare tags
     *
     * @param string[] $tags
     * @return string[]
     */
    protected function _tags($tags)
    {
        foreach ($tags as $key => $tag) {
            $tags[$key] = $this->_id($tag);
        }
        return $tags;
    }
    /**
     * @inheritDoc
     */
    public function save($data, $cache_id = null, $tags = [], $specific_lifetime = false, $priority = 8)
    {
        if ($this->get_option('disable_save')) {
            return true;
        }
        $tags = $this->_tags($tags);
        return parent::save($data, $cache_id, $tags, $specific_lifetime, $priority);
    }
    /**
     * Clean cache entries
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => remove too old cache entries ($tags is not used)
     * 'matchingTag'    => remove cache entries matching all given tags
     *                     ($tags can be an array of strings or a single string)
     * 'notMatchingTag' => remove cache entries not matching one of the given tags
     *                     ($tags can be an array of strings or a single string)
     * 'matchingAnyTag' => remove cache entries matching any given tags
     *                     ($tags can be an array of strings or a single string)
     *
     * @param string $mode
     * @param string[] $tags
     * @throws \Zend_Cache_Exception
     * @return bool True if ok
     */
    public function clean($mode = 'all', $tags = [])
    {
        $tags = $this->_tags($tags);
        return parent::clean($mode, $tags);
    }
    /**
     * Return an array of stored cache ids which match given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of matching cache ids (string)
     */
    public function get_ids_matching_tags($tags = [])
    {
        $tags = $this->_tags($tags);
        return parent::get_ids_matching_tags($tags);
    }
    /**
     * Return an array of stored cache ids which don't match given tags
     *
     * In case of multiple tags, a logical OR is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of not matching cache ids (string)
     */
    public function get_ids_not_matching_tags($tags = [])
    {
        $tags = $this->_tags($tags);
        return parent::get_ids_not_matching_tags($tags);
    }
    /**
     * Validate a cache id or a tag (security, reliable filenames, reserved prefixes...)
     *
     * Throw an exception if a problem is found
     *
     * @param  string $string Cache id or tag
     * @throws Zend_Cache_Exception
     * @return void
     */
    protected function _validate_id_or_tag($string)
    {
        if ($this->_backend instanceof Redis) {
            if (!is_string($string)) {
                Zend_Cache::throw_exception('Invalid id or tag : must be a string');
            }
            if (substr($string, 0, 9) == 'internal-') {
                Zend_Cache::throw_exception('"internal-*" ids or tags are reserved');
            }
            if (!preg_match('~^[a-zA-Z0-9_{}]+$~D', $string)) {
                Zend_Cache::throw_exception("Invalid id or tag '{$string}' : must use only [a-zA-Z0-9_{}]");
            }
            return;
        }
        parent::_validate_id_or_tag($string);
    }
    /**
     * Set the backend
     *
     * @param  \Zend_Cache_Backend $backendObject
     * @return void
     */
    public function set_backend(\Zend_Cache_Backend $backend_object)
    {
        $backend_object = $this->_decorate_backend($backend_object);
        parent::set_backend($backend_object);
    }
    /**
     * Decorate cache backend with additional functionality
     *
     * @param \Zend_Cache_Backend $backendObject
     * @return \Zend_Cache_Backend
     */
    protected function _decorate_backend(\Zend_Cache_Backend $backend_object)
    {
        if (!is_array($this->_specific_options['backend_decorators'])) {
            \Zend_Cache::throw_exception("'backend_decorator' option should be an array");
        }
        foreach ($this->_specific_options['backend_decorators'] as $decorator_name => $decorator_options) {
            if (!is_array($decorator_options) || !array_key_exists('class', $decorator_options)) {
                \Zend_Cache::throw_exception("Concrete decorator options in '" . $decorator_name . "' should be an array containing 'class' key");
            }
            $class_options = array_key_exists('options', $decorator_options) ? $decorator_options['options'] : [];
            $class_options['concrete_backend'] = $backend_object;
            if (!class_exists($decorator_options['class'])) {
                \Zend_Cache::throw_exception("Class '" . $decorator_options['class'] . "' specified in '" . $decorator_name . "' does not exist");
            }
            $backend_object = new $decorator_options['class']($class_options);
            if (!$backend_object instanceof \Magento\Framework\Cache\Backend\Decorator\Abstract_Decorator) {
                \Zend_Cache::throw_exception("Decorator in '" . $decorator_name . "' should extend \\Magento\\Framework\\Cache\\Backend\\Decorator\\AbstractDecorator");
            }
        }
        return $backend_object;
    }
    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}