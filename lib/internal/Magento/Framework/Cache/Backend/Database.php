<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
/**
 * Tables declaration:
 *
 * CREATE TABLE IF NOT EXISTS `cache` (
 *      `id` VARCHAR(255) NOT NULL,
 *      `data` mediumblob,
 *      `create_time` int(11),
 *      `update_time` int(11),
 *      `expire_time` int(11),
 *      PRIMARY KEY  (`id`),
 *      KEY `IDX_EXPIRE_TIME` (`expire_time`)
 * )ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 * CREATE TABLE IF NOT EXISTS `cache_tag` (
 *      `tag` VARCHAR(255) NOT NULL,
 *      `cache_id` VARCHAR(255) NOT NULL,
 *      KEY `IDX_TAG` (`tag`),
 *      KEY `IDX_CACHE_ID` (`cache_id`),
 *      CONSTRAINT `FK_CORE_CACHE_TAG` FOREIGN KEY (`cache_id`)
 *      REFERENCES `cache` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */
namespace Magento\Framework\Cache\Backend;

use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Exception\Cache_Exception;
/**
 * Database cache backend.
 *
 * Magento-native cache backend using database storage.
 */
class Database extends Abstract_Backend implements Extended_Backend_Interface
{
    /**
     * Available options
     *
     * @var array available options
     */
    protected $_options = ['adapter' => '', 'adapter_callback' => '', 'data_table' => '', 'data_table_callback' => '', 'tags_table' => '', 'tags_table_callback' => '', 'store_data' => true, 'infinite_loop_flag' => false];
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection = null;
    /**
     * Constructor
     *
     * @param array $options associative array of options
     * @throws CacheException
     */
    public function __construct($options = [])
    {
        parent::__construct($options);
        if (empty($this->_options['adapter_callback'])) {
            if (!$this->_options['adapter'] instanceof \Magento\Framework\DB\Adapter\Adapter_Interface) {
                throw new Cache_Exception(__('Option "adapter" should be declared and extend \Magento\Framework\DB\Adapter\AdapterInterface!'));
            }
        }
        if (empty($this->_options['data_table']) && empty($this->_options['data_table_callback'])) {
            throw new Cache_Exception(__('Option "data_table" or "data_table_callback" should be declared!'));
        }
        if (empty($this->_options['tags_table']) && empty($this->_options['tags_table_callback'])) {
            throw new Cache_Exception(__('Option "tags_table" or "tags_table_callback" should be declared!'));
        }
    }
    /**
     * Get DB adapter
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws CacheException
     */
    protected function _get_connection()
    {
        if (!$this->_connection) {
            if (!empty($this->_options['adapter_callback'])) {
                $connection = call_user_func($this->_options['adapter_callback']);
            } else {
                $connection = $this->_options['adapter'];
            }
            if (!$connection instanceof \Magento\Framework\DB\Adapter\Adapter_Interface) {
                throw new Cache_Exception(__('DB Adapter should be declared and extend \Magento\Framework\DB\Adapter\AdapterInterface'));
            } else {
                $this->_connection = $connection;
            }
        }
        return $this->_connection;
    }
    /**
     * Get table name where data is stored
     *
     * @return string
     * @throws CacheException
     */
    protected function _get_data_table()
    {
        if (empty($this->_options['data_table'])) {
            $this->set_option('data_table', call_user_func($this->_options['data_table_callback']));
            if (empty($this->_options['data_table'])) {
                throw new Cache_Exception(__('Failed to detect data_table option'));
            }
        }
        return $this->_options['data_table'];
    }
    /**
     * Get table name where tags are stored
     *
     * @return string
     * @throws CacheException
     */
    protected function _get_tags_table()
    {
        if (empty($this->_options['tags_table'])) {
            $this->set_option('tags_table', call_user_func($this->_options['tags_table_callback']));
            if (empty($this->_options['tags_table'])) {
                throw new Cache_Exception(__('Failed to detect tags_table option'));
            }
        }
        return $this->_options['tags_table'];
    }
    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     *
     * @param string $id Cache id
     * @param boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     * @throws CacheException
     */
    public function load($id, $do_not_test_cache_validity = false)
    {
        if ($this->_options['store_data'] && !$this->_options['infinite_loop_flag']) {
            $this->_options['infinite_loop_flag'] = true;
            $select = $this->_get_connection()->select()->from($this->_get_data_table(), 'data')->where('id=:cache_id');
            if (!$do_not_test_cache_validity) {
                $select->where('expire_time=0 OR expire_time>?', time());
            }
            $result = $this->_get_connection()->fetch_one($select, ['cache_id' => $id]);
            $this->_options['infinite_loop_flag'] = false;
            return $result;
        } else {
            return false;
        }
    }
    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param string $id cache id
     * @return mixed|false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     * @throws CacheException
     */
    public function test($id)
    {
        if ($this->_options['store_data'] && !$this->_options['infinite_loop_flag']) {
            $this->_options['infinite_loop_flag'] = true;
            $select = $this->_get_connection()->select()->from($this->_get_data_table(), 'update_time')->where('id=:cache_id')->where('expire_time=0 OR expire_time>?', time());
            $result = $this->_get_connection()->fetch_one($select, ['cache_id' => $id]);
            $this->_options['infinite_loop_flag'] = false;
            return $result;
        } else {
            return false;
        }
    }
    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param string $data Datas to cache
     * @param string $id Cache id
     * @param string[] $tags Array of strings, the cache record will be tagged by each string entry
     * @param int|bool $specificLifetime Integer to set a specific lifetime or null for infinite lifetime
     * @return bool true if no problem
     * @throws \Zend_Db_Statement_Exception
     * @throws CacheException
     */
    public function save($data, $id, $tags = [], $specific_lifetime = null)
    {
        $result = false;
        if (!$this->_options['infinite_loop_flag']) {
            $this->_options['infinite_loop_flag'] = true;
            $result = true;
            if ($this->_options['store_data']) {
                $connection = $this->_get_connection();
                $data_table = $this->_get_data_table();
                $lifetime = $this->get_lifetime($specific_lifetime);
                $time = time();
                $expire = $lifetime === 0 || $lifetime === null ? 0 : $time + $lifetime;
                $id_col = $connection->quote_identifier('id');
                $data_col = $connection->quote_identifier('data');
                $create_col = $connection->quote_identifier('create_time');
                $update_col = $connection->quote_identifier('update_time');
                $expire_col = $connection->quote_identifier('expire_time');
                $query = "INSERT INTO {$data_table} ({$id_col}, {$data_col}, {$create_col}, {$update_col}, {$expire_col}) " . "VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE {$data_col}=VALUES({$data_col}), " . "{$update_col}=VALUES({$update_col}), {$expire_col}=VALUES({$expire_col})";
                $result = $connection->query($query, [$id, $data, $time, $time, $expire])->row_count();
            }
            if ($result) {
                $result = $this->_save_tags($id, $tags);
            }
            $this->_options['infinite_loop_flag'] = false;
        }
        return $result;
    }
    /**
     * Remove a cache record
     *
     * @param string $id Cache id
     * @return int|boolean Number of affected rows or false on failure
     * @throws CacheException
     */
    public function remove($id)
    {
        if ($this->_options['store_data'] && !$this->_options['infinite_loop_flag']) {
            $this->_options['infinite_loop_flag'] = true;
            $result = $this->_get_connection()->delete($this->_get_data_table(), ['id=?' => $id]);
            $this->_options['infinite_loop_flag'] = false;
            return $result;
        }
        return false;
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
     * @param string $mode Clean mode
     * @param string[] $tags Array of tags
     * @return boolean true if no problem
     * @throws CacheException
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, $tags = [])
    {
        $result = false;
        if (!$this->_options['infinite_loop_flag']) {
            $this->_options['infinite_loop_flag'] = true;
            $connection = $this->_get_connection();
            switch ($mode) {
                case Cache_Constants::CLEANING_MODE_ALL:
                    $result = $this->clean_all($connection);
                    break;
                case Cache_Constants::CLEANING_MODE_OLD:
                    $result = $this->clean_old($connection);
                    break;
                case Cache_Constants::CLEANING_MODE_MATCHING_TAG:
                case Cache_Constants::CLEANING_MODE_NOT_MATCHING_TAG:
                case Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG:
                    $result = $this->_clean_by_tags($mode, $tags);
                    break;
                default:
                    throw new Cache_Exception(__('Invalid mode for clean() method'));
            }
            $this->_options['infinite_loop_flag'] = false;
        }
        return $result;
    }
    /**
     * Return an array of stored cache ids
     *
     * @return string[] array of stored cache ids (string)
     * @throws CacheException
     */
    public function get_ids()
    {
        if ($this->_options['store_data']) {
            $select = $this->_get_connection()->select()->from($this->_get_data_table(), 'id');
            return $this->_get_connection()->fetch_col($select);
        } else {
            return [];
        }
    }
    /**
     * Return an array of stored tags
     *
     * @return string[] array of stored tags (string)
     * @throws CacheException
     */
    public function get_tags()
    {
        $select = $this->_get_connection()->select()->from($this->_get_tags_table(), 'tag')->distinct(true);
        return $this->_get_connection()->fetch_col($select);
    }
    /**
     * Return an array of stored cache ids which match given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of matching cache ids (string)
     * @throws CacheException
     */
    public function get_ids_matching_tags($tags = [])
    {
        $select = $this->_get_connection()->select()->from($this->_get_tags_table(), 'cache_id')->distinct(true)->where('tag IN(?)', $tags)->group('cache_id')->having('COUNT(cache_id)=' . count($tags));
        return $this->_get_connection()->fetch_col($select);
    }
    /**
     * Return an array of stored cache ids which don't match given tags
     *
     * In case of multiple tags, a logical OR is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of not matching cache ids (string)
     * @throws CacheException
     */
    public function get_ids_not_matching_tags($tags = [])
    {
        return array_diff($this->get_ids(), $this->get_ids_matching_any_tags($tags));
    }
    /**
     * Return an array of stored cache ids which match any given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of any matching cache ids (string)
     * @throws CacheException
     */
    public function get_ids_matching_any_tags($tags = [])
    {
        $select = $this->_get_connection()->select()->from($this->_get_tags_table(), 'cache_id')->distinct(true)->where('tag IN(?)', $tags);
        return $this->_get_connection()->fetch_col($select);
    }
    /**
     * Return the filling percentage of the backend storage
     *
     * @return int integer between 0 and 100
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
     * @param string $id cache id
     * @return array|false array of metadatas (false if the cache id is not found)
     * @throws CacheException
     */
    public function get_metadatas($id)
    {
        $select = $this->_get_connection()->select()->from($this->_get_tags_table(), 'tag')->where('cache_id=?', $id);
        $tags = $this->_get_connection()->fetch_col($select);
        $select = $this->_get_connection()->select()->from($this->_get_data_table())->where('id=?', $id);
        $data = $this->_get_connection()->fetch_row($select);
        $res = false;
        if ($data) {
            $res = ['expire' => $data['expire_time'], 'mtime' => $data['update_time'], 'tags' => $tags];
        }
        return $res;
    }
    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id cache id
     * @param int $extraLifetime
     * @return boolean true if ok
     * @throws CacheException
     */
    public function touch($id, $extra_lifetime)
    {
        if ($this->_options['store_data']) {
            return $this->_get_connection()->update($this->_get_data_table(), ['expire_time' => new \Zend_Db_Expr('expire_time+' . $extra_lifetime)], ['id=?' => $id, 'expire_time = 0 OR expire_time>?' => time()]);
        } else {
            return true;
        }
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
     * Save tags related to specific id
     *
     * @param string $id
     * @param string[] $tags
     * @return bool
     * @throws CacheException
     */
    protected function _save_tags($id, $tags)
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }
        if (empty($tags)) {
            return true;
        }
        $connection = $this->_get_connection();
        $tags_table = $this->_get_tags_table();
        $select = $connection->select()->from($tags_table, 'tag')->where('cache_id=?', $id)->where('tag IN(?)', $tags);
        $existing_tags = $connection->fetch_col($select);
        $insert_tags = array_diff($tags, $existing_tags);
        if (!empty($insert_tags)) {
            $query = 'INSERT IGNORE INTO ' . $tags_table . ' (tag, cache_id) VALUES ';
            $bind = [];
            $lines = [];
            foreach ($insert_tags as $tag) {
                $lines[] = '(?, ?)';
                $bind[] = $tag;
                $bind[] = $id;
            }
            $query .= implode(',', $lines);
            $connection->query($query, $bind);
        }
        $result = true;
        return $result;
    }
    /**
     * Remove cache data by tags with specified mode
     *
     * @param string $mode
     * @param string[] $tags
     * @return bool
     * @throws CacheException
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _clean_by_tags($mode, $tags)
    {
        if ($this->_options['store_data']) {
            $connection = $this->_get_connection();
            $select = $connection->select()->from($this->_get_tags_table(), 'cache_id');
            switch ($mode) {
                case Cache_Constants::CLEANING_MODE_MATCHING_TAG:
                    $select->where('tag IN (?)', $tags)->group('cache_id')->having('COUNT(cache_id)=' . count($tags));
                    break;
                case Cache_Constants::CLEANING_MODE_NOT_MATCHING_TAG:
                    $select->where('tag NOT IN (?)', $tags);
                    break;
                case Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG:
                    $select->where('tag IN (?)', $tags);
                    break;
                default:
                    throw new Cache_Exception(__('Invalid mode for _cleanByTags() method'));
            }
            $result = true;
            $ids = [];
            $counter = 0;
            $stmt = $connection->query($select);
            while ($row = $stmt->fetch()) {
                $ids[] = $row['cache_id'];
                $counter++;
                if ($counter > 100) {
                    $result = $result && $connection->delete($this->_get_data_table(), ['id IN (?)' => $ids]);
                    $ids = [];
                    $counter = 0;
                }
            }
            if (!empty($ids)) {
                $result = $result && $connection->delete($this->_get_data_table(), ['id IN (?)' => $ids]);
            }
            return $result;
        } else {
            return true;
        }
    }
    /**
     * Clean all cache entries
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return bool
     * @throws CacheException
     */
    private function clean_all(\Magento\Framework\DB\Adapter\Adapter_Interface $connection)
    {
        if ($this->_options['store_data']) {
            $result = $connection->query('TRUNCATE TABLE ' . $this->_get_data_table());
        } else {
            $result = true;
        }
        $result = $result && $connection->query('TRUNCATE TABLE ' . $this->_get_tags_table());
        return $result;
    }
    /**
     * Clean old cache entries
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return bool
     * @throws CacheException
     */
    private function clean_old(\Magento\Framework\DB\Adapter\Adapter_Interface $connection)
    {
        if ($this->_options['store_data']) {
            $result = $connection->delete($this->_get_data_table(), ['expire_time> ?' => 0, 'expire_time<= ?' => time()]);
            return $result;
        } else {
            $result = true;
            return $result;
        }
    }
}