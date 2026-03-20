<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
/**
 * MongoDb cache backend
 */
namespace Magento\Framework\Cache\Backend;

use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Exception\Cache_Exception;
/**
 * Magento-native MongoDB cache backend
 */
class Mongo_Db extends Abstract_Backend implements Extended_Backend_Interface
{
    /**
     * Infinite expiration time
     */
    public const EXPIRATION_TIME_INFINITE = 0;
    /**#@+
     * Available comparison modes. Used for composing queries to search by tags
     */
    public const COMPARISON_MODE_MATCHING_TAG = Cache_Constants::CLEANING_MODE_MATCHING_TAG;
    public const COMPARISON_MODE_NOT_MATCHING_TAG = Cache_Constants::CLEANING_MODE_NOT_MATCHING_TAG;
    public const COMPARISON_MODE_MATCHING_ANY_TAG = Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG;
    /**#@-*/
    /**
     * @var \MongoCollection|null
     */
    protected $_collection = null;
    /**
     * List of available options
     *
     * @var array
     */
    protected $_options = [
        'connection_string' => 'mongodb://localhost:27017',
        // MongoDB connection string
        'mongo_options' => [],
        // MongoDB connection options
        'db' => '',
        // Name of a database to be used for cache storage
        'collection' => 'cache',
    ];
    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('mongo') || !version_compare(\Mongo::VERSION, '1.2.11', '>=')) {
            throw new Cache_Exception(__("At least 1.2.11 version of 'mongo' extension is required for using MongoDb cache backend"));
        }
        if (empty($options['db'])) {
            throw new Cache_Exception(__("'db' option is not specified"));
        }
        parent::__construct($options);
    }
    /**
     * Get collection
     *
     * @return \MongoCollection
     */
    protected function _get_collection()
    {
        if (null === $this->_collection) {
            $connection = new \Mongo($this->_options['connection_string'], $this->_options['mongo_options']);
            $database = $connection->select_db($this->_options['db']);
            $this->_collection = $database->select_collection($this->_options['collection']);
        }
        return $this->_collection;
    }
    /**
     * Return an array of stored cache ids
     *
     * @return string[] array of stored cache ids (string)
     */
    public function get_ids()
    {
        return array_keys(iterator_to_array($this->_get_collection()->find([], ['_id'])));
    }
    /**
     * Return an array of stored tags
     *
     * @return string[] array of stored tags (string)
     */
    public function get_tags()
    {
        $result = $this->_get_collection()->distinct('tags');
        return $result ?: [];
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
        $query = $this->_get_query_matching_tags($tags, self::COMPARISON_MODE_MATCHING_TAG);
        if (empty($query)) {
            return [];
        }
        $result = $this->_get_collection()->find($query, ['_id']);
        return array_keys(iterator_to_array($result));
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
        $query = $this->_get_query_matching_tags($tags, self::COMPARISON_MODE_NOT_MATCHING_TAG);
        if (empty($query)) {
            return [];
        }
        $result = $this->_get_collection()->find($query, ['_id']);
        return array_keys(iterator_to_array($result));
    }
    /**
     * Return an array of stored cache ids which match any given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of any matching cache ids (string)
     */
    public function get_ids_matching_any_tags($tags = [])
    {
        $query = $this->_get_query_matching_tags($tags, self::COMPARISON_MODE_MATCHING_ANY_TAG);
        if (empty($query)) {
            return [];
        }
        $result = $this->_get_collection()->find($query, ['_id']);
        return array_keys(iterator_to_array($result));
    }
    /**
     * Get query to filter by specified tags and comparison mode
     *
     * @param string[] $tags
     * @param string $comparisonMode
     * @return array
     */
    protected function _get_query_matching_tags(array $tags, $comparison_mode)
    {
        $operators = [self::COMPARISON_MODE_MATCHING_TAG => '$and', self::COMPARISON_MODE_NOT_MATCHING_TAG => '$nor', self::COMPARISON_MODE_MATCHING_ANY_TAG => '$or'];
        if (!isset($operators[$comparison_mode])) {
            throw new Cache_Exception(__('Incorrect comparison mode specified: %1', $comparison_mode));
        }
        $operator = $operators[$comparison_mode];
        $query = [];
        foreach ($tags as $tag) {
            $query[$operator][] = ['tags' => $this->_quote_string($tag)];
        }
        return $query;
    }
    /**
     * Return the filling percentage of the backend storage
     *
     * @return int integer between 0 and 100
     * TODO: implement basing on info from MongoDB server
     */
    public function get_filling_percentage()
    {
        return 1;
    }
    /**
     * Return an array of metadatas for the given cache id
     *
     * The array must include these keys :
     * - expire : the expire timestamp
     * - tags : a string array of tags
     * - mtime : timestamp of last modification time
     *
     * @param string $cacheId cache id
     * @return array|false array of metadatas (false if the cache id is not found)
     */
    public function get_metadatas($cache_id)
    {
        $result = $this->_get_collection()->find_one(['_id' => $this->_quote_string($cache_id)], ['expire', 'tags', 'mtime']);
        return $result === null ? false : $result;
    }
    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $cacheId cache id
     * @param int $extraLifetime
     * @return boolean true if ok
     */
    public function touch($cache_id, $extra_lifetime)
    {
        $time = time();
        $condition = ['_id' => $this->_quote_string($cache_id), 'expire' => ['$gt' => $time]];
        $update = ['$set' => ['mtime' => $time], '$inc' => ['expire' => (int) $extra_lifetime]];
        $result = $this->_get_collection()->update($condition, $update);
        return (bool) ($result['ok'] ?? false);
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
        return ['automatic_cleaning' => true, 'tags' => true, 'expired_read' => true, 'priority' => false, 'infinite_lifetime' => true, 'get_list' => true];
    }
    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     *
     * @param string $cacheId Cache id
     * @param boolean $notTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|bool cached data. Return false if nothing found
     */
    public function load($cache_id, $not_test_cache_validity = false)
    {
        $query = ['_id' => $this->_quote_string($cache_id)];
        if (!$not_test_cache_validity) {
            $query['$or'] = [['expire' => self::EXPIRATION_TIME_INFINITE], ['expire' => ['$gt' => time()]]];
        }
        $result = $this->_get_collection()->find_one($query, ['data']);
        return $result ? $result['data']->bin : false;
    }
    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $cacheId cache id
     * @return int|bool "last modified" timestamp of the available cache record or false if cache is not available
     */
    public function test($cache_id)
    {
        $result = $this->_get_collection()->find_one(['_id' => $this->_quote_string($cache_id), '$or' => [['expire' => self::EXPIRATION_TIME_INFINITE], ['expire' => ['$gt' => time()]]]], ['mtime']);
        return $result ? $result['mtime'] : false;
    }
    /**
     * Save some string data into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param string $data Datas to cache
     * @param string $cacheId Cache id
     * @param string[] $tags Array of strings, the cache record will be tagged by each string entry
     * @param int|bool $specificLifetime If != false, set a specific lifetime (null => infinite lifetime)
     * @return boolean true if no problem
     */
    public function save($data, $cache_id, $tags = [], $specific_lifetime = null)
    {
        $lifetime = $this->get_lifetime($specific_lifetime);
        $time = time();
        $expire = $lifetime === null ? self::EXPIRATION_TIME_INFINITE : $time + $lifetime;
        $tags = array_map([$this, '_quoteString'], $tags);
        $document = ['_id' => $this->_quote_string($cache_id), 'data' => new \Mongo_Bin_Data($this->_quote_string($data), \Mongo_Bin_Data::BYTE_ARRAY), 'tags' => $tags, 'mtime' => $time, 'expire' => $expire];
        return $this->_get_collection()->save($document);
    }
    /**
     * Remove a cache record
     *
     * @param  string $cacheId Cache id
     * @return boolean True if no problem
     */
    public function remove($cache_id)
    {
        return $this->_get_collection()->remove(['_id' => $this->_quote_string($cache_id)]);
    }
    /**
     * Clean some cache records
     *
     * Available modes are :
     * CacheConstants::CLEANING_MODE_ALL (default)    => remove all cache entries ($tags is not used)
     * CacheConstants::CLEANING_MODE_OLD              => remove too old cache entries ($tags is not used)
     * CacheConstants::CLEANING_MODE_MATCHING_TAG     => remove cache entries matching all given tags
     *                                               ($tags can be an array of strings or a single string)
     * CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG => remove cache entries not {matching one of the given tags}
     *                                               ($tags can be an array of strings or a single string)
     * CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG => remove cache entries matching any given tags
     *                                               ($tags can be an array of strings or a single string)
     *
     * @param  string $mode Clean mode
     * @param  string[] $tags Array of tags
     * @return bool true if no problem
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, $tags = [])
    {
        $result = false;
        switch ($mode) {
            case Cache_Constants::CLEANING_MODE_ALL:
                $result = $this->_get_collection()->drop();
                $result = (bool) ($result['ok'] ?? false);
                break;
            case Cache_Constants::CLEANING_MODE_OLD:
                $query = ['expire' => ['$ne' => self::EXPIRATION_TIME_INFINITE, '$lte' => time()]];
                break;
            case Cache_Constants::CLEANING_MODE_MATCHING_TAG:
            case Cache_Constants::CLEANING_MODE_NOT_MATCHING_TAG:
            case Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG:
                $query = $this->_get_query_matching_tags((array) $tags, $mode);
                break;
            default:
                throw new Cache_Exception(__('Unsupported cleaning mode: %1', $mode));
        }
        if (!empty($query)) {
            $remove_result = $this->_get_collection()->remove($query);
            $result = (bool) ($remove_result['ok'] ?? false);
        }
        return $result;
    }
    /**
     * Quote specified value to be used in query as string
     *
     * @param string $value
     * @return string
     */
    protected function _quote_string($value)
    {
        return (string) $value;
    }
}