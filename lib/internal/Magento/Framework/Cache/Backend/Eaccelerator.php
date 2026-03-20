<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Backend;

use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Exception\Cache_Exception;
/**
 * eAccelerator cache backend
 *
 * @deprecated eAccelerator extension is no longer maintained (last update 2012).
 *             Use Symfony Cache with Redis or File adapters instead.
 * @see \Magento\Framework\Cache\Frontend\Adapter\Symfony
 */
class Eaccelerator extends Abstract_Backend implements Extended_Backend_Interface
{
    /**
     * Log message
     */
    public const TAGS_UNSUPPORTED_BY_CLEAN_OF_EACCELERATOR_BACKEND = 'Magento\Framework\Cache\Backend\Eaccelerator::clean() : tags are unsupported by the Eaccelerator backend';
    public const TAGS_UNSUPPORTED_BY_SAVE_OF_EACCELERATOR_BACKEND = 'Magento\Framework\Cache\Backend\Eaccelerator::save() : tags are unsupported by the Eaccelerator backend';
    /**
     * Constructor
     *
     * @param array $options associative array of options
     * @throws CacheException
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('eaccelerator')) {
            throw new Cache_Exception(__('The eaccelerator extension must be loaded for using this backend!'));
        }
        parent::__construct($options);
    }
    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * WARNING $doNotTestCacheValidity=true is unsupported by the Eaccelerator backend
     *
     * @param  string  $id                     cache id
     * @param  boolean $doNotTestCacheValidity if set to true, the cache validity won't be tested
     * @return string cached datas (or false)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($id, $do_not_test_cache_validity = false)
    {
        $tmp = eaccelerator_get($id);
        if (is_array($tmp)) {
            return $tmp[0];
        }
        return false;
    }
    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id cache id
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id)
    {
        $tmp = eaccelerator_get($id);
        if (is_array($tmp)) {
            return $tmp[1];
        }
        return false;
    }
    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param string $data datas to cache
     * @param string $id cache id
     * @param string[] $tags array of strings, the cache record will be tagged by each string entry
     * @param int|bool $specificLifetime Integer to set a specific lifetime or null for infinite lifetime
     * @return bool true if no problem
     */
    public function save($data, $id, $tags = [], $specific_lifetime = null)
    {
        $lifetime = $this->get_lifetime($specific_lifetime);
        $result = eaccelerator_put($id, [$data, time(), $lifetime], $lifetime);
        if (count($tags) > 0) {
            $this->log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_EACCELERATOR_BACKEND);
        }
        return $result;
    }
    /**
     * Remove a cache record
     *
     * @param  string $id cache id
     * @return bool true if no problem
     */
    public function remove($id)
    {
        return eaccelerator_rm($id);
    }
    /**
     * Clean some cache records
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => unsupported
     * 'matchingTag'    => unsupported
     * 'notMatchingTag' => unsupported
     * 'matchingAnyTag' => unsupported
     *
     * @param string $mode clean mode
     * @param array $tags array of tags
     * @throws CacheException
     * @return bool true if no problem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, $tags = [])
    {
        switch ($mode) {
            case Cache_Constants::CLEANING_MODE_ALL:
            case 'all':
                return eaccelerator_clean();
            case Cache_Constants::CLEANING_MODE_OLD:
            case 'old':
                $this->log("Magento\\Framework\\Cache\\Backend\\Eaccelerator::clean() : " . 'CLEANING_MODE_OLD is unsupported by the Eaccelerator backend');
                return false;
            case Cache_Constants::CLEANING_MODE_MATCHING_TAG:
            case Cache_Constants::CLEANING_MODE_NOT_MATCHING_TAG:
            case Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG:
            case 'matchingTag':
            case 'notMatchingTag':
            case 'matchingAnyTag':
                $this->log(self::TAGS_UNSUPPORTED_BY_CLEAN_OF_EACCELERATOR_BACKEND);
                return false;
            default:
                throw new Cache_Exception(__('Invalid mode for clean() method'));
        }
    }
    /**
     * Return the filling percentage of the backend storage
     *
     * @throws CacheException
     * @return int integer between 0 and 100
     */
    public function get_filling_percentage()
    {
        $mem = eaccelerator_info();
        $mem_size = $mem['memorySize'];
        $mem_available = $mem['memoryAvailable'];
        $mem_used = $mem_size - $mem_available;
        if ($mem_size == 0) {
            throw new Cache_Exception(__('Cannot get eaccelerator memory size'));
        }
        if ($mem_used > $mem_size) {
            return 100;
        }
        return (int) (100.0 * ($mem_used / $mem_size));
    }
    /**
     * Return an array of stored tags
     *
     * @return string[] array of stored tags (string)
     */
    public function get_tags()
    {
        $this->log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_EACCELERATOR_BACKEND);
        return [];
    }
    /**
     * Return an array of stored cache ids which match given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param array $tags array of tags
     * @return string[] array of matching cache ids (string)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_ids_matching_tags($tags = [])
    {
        $this->log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_EACCELERATOR_BACKEND);
        return [];
    }
    /**
     * Return an array of stored cache ids which don't match given tags
     *
     * In case of multiple tags, a logical OR is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of not matching cache ids (string)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_ids_not_matching_tags($tags = [])
    {
        $this->log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_EACCELERATOR_BACKEND);
        return [];
    }
    /**
     * Return an array of stored cache ids which match any given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of any matching cache ids (string)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_ids_matching_any_tags($tags = [])
    {
        $this->log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_EACCELERATOR_BACKEND);
        return [];
    }
    /**
     * Return an array of stored cache ids
     *
     * @return string[] array of stored cache ids (string)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function get_ids()
    {
        $res = [];
        $array = eaccelerator_list_keys();
        foreach ($array as $key => $info) {
            $res[] = $key;
        }
        return $res;
    }
    /**
     * Return an array of metadatas for the given cache id
     *
     * The array must include these keys :
     * - expire : the expire timestamp
     * - tags : a string array of tags
     * - mtime : timestamp of last modification time
     *
     * @param string $id cache id
     * @return array|false array of metadatas (false if the cache id is not found)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function get_metadatas($id)
    {
        $tmp = eaccelerator_get($id);
        if (is_array($tmp)) {
            $data = $tmp[0];
            $mtime = $tmp[1];
            if (!isset($tmp[2])) {
                // because this record is only with 1.7 release
                // if old cache records are still there...
                return false;
            }
            $lifetime = $tmp[2];
            return ['expire' => $mtime + $lifetime, 'tags' => [], 'mtime' => $mtime];
        }
        return false;
    }
    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id cache id
     * @param int $extraLifetime
     * @return bool true if ok
     */
    public function touch($id, $extra_lifetime)
    {
        $tmp = eaccelerator_get($id);
        if (is_array($tmp)) {
            $data = $tmp[0];
            $mtime = $tmp[1];
            if (!isset($tmp[2])) {
                // because this record is only with 1.7 release
                // if old cache records are still there...
                return false;
            }
            $lifetime = $tmp[2];
            $new_lifetime = $lifetime - (time() - $mtime) + $extra_lifetime;
            if ($new_lifetime <= 0) {
                return false;
            }
            eaccelerator_put($id, [$data, time(), $new_lifetime], $new_lifetime);
            return true;
        }
        return false;
    }
    /**
     * Return an associative array of capabilities (booleans) of the backend
     *
     * The array must include these keys :
     * - automatic_cleaning (is automating cleaning necessary)
     * - tags (are tags supported)
     * - expired_read (is it possible to read expired cache records
     *                 (for doNotTestCacheValidity option for example))
     * - priority does the backend deal with priority when saving
     * - infinite_lifetime (is infinite lifetime can work with this backend)
     * - get_list (is it possible to get the list of cache ids and the complete list of tags)
     *
     * @return array associative of with capabilities
     */
    public function get_capabilities()
    {
        return ['automatic_cleaning' => false, 'tags' => false, 'expired_read' => false, 'priority' => false, 'infinite_lifetime' => false, 'get_list' => true];
    }
}