<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Backend;

use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Exception\Cache_Exception;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
/**
 * Memcached cache backend with chunking support
 *
 * Magento-native implementation of Memcached backend with automatic chunking
 * for large values that exceed memcached's slab size limits.
 *
 * @deprecated Use Symfony Cache with MemcachedAdapter for better performance and PSR-6 compliance.
 * @see \Symfony\Component\Cache\Adapter\MemcachedAdapter
 */
class Memcached extends Abstract_Backend implements Extended_Backend_Interface
{
    /**
     * Maximum chunk of data that could be saved in one memcache cell (1 MiB)
     */
    public const DEFAULT_SLAB_SIZE = 1048576;
    /**
     * Used to tell chunked data from ordinary
     */
    public const CODE_WORD = '{splitted}';
    /**
     * @var \Memcached
     */
    private $memcached;
    /**
     * @var array
     */
    private $servers = [];
    /**
     * @var string
     */
    private $prefix = '';
    /**
     * Constructor
     *
     * @param array $options Memcached configuration options
     * @throws LocalizedException
     * @throws CacheException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        // Validate slab_size
        if (isset($options['slab_size'])) {
            if (!is_numeric($options['slab_size']) || $options['slab_size'] <= 0) {
                throw new Localized_Exception(new Phrase('Invalid value for the node <slab_size>. Expected to be positive integer.'));
            }
            $this->_options['slab_size'] = (int) $options['slab_size'];
        } else {
            $this->_options['slab_size'] = self::DEFAULT_SLAB_SIZE;
        }
        // Set compression (default: true)
        $this->_options['compression'] = $options['compression'] ?? true;
        // Set prefix
        $this->prefix = $options['prefix'] ?? '';
        // Initialize memcached connection
        if (!extension_loaded('memcached')) {
            throw new Cache_Exception(__('The memcached extension must be loaded for using this backend!'));
        }
        $this->memcached = new \Memcached();
        // Add servers
        if (isset($options['servers']) && is_array($options['servers'])) {
            $this->servers = $options['servers'];
            foreach ($this->servers as $server) {
                $host = $server['host'] ?? 'localhost';
                $port = $server['port'] ?? 11211;
                $weight = $server['weight'] ?? 1;
                $this->memcached->add_server($host, $port, $weight);
            }
        } else {
            // Default server
            $this->memcached->add_server('localhost', 11211);
        }
        // Set compression option
        if ($this->_options['compression']) {
            $this->memcached->set_option(\Memcached::OPT_COMPRESSION, true);
        }
    }
    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param string $id Cache id
     * @param bool $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false Cached data or false
     */
    public function load($id, $do_not_test_cache_validity = false)
    {
        $data = $this->load_direct($id, $do_not_test_cache_validity);
        if (is_string($data) && substr($data, 0, strlen(self::CODE_WORD)) == self::CODE_WORD) {
            // Seems we've got chunked data
            $arr = explode('|', $data);
            $chunks = isset($arr[1]) ? $arr[1] : false;
            $chunk_data = [];
            if ($chunks && is_numeric($chunks)) {
                for ($i = 0; $i < $chunks; $i++) {
                    $chunk = $this->load_direct($this->get_chunk_id($id, $i), $do_not_test_cache_validity);
                    if (false === $chunk) {
                        // Some chunk in chain was not found, clean the mess and return nothing
                        $this->clean_the_mess($id, (int) $chunks);
                        return false;
                    }
                    $chunk_data[] = $chunk;
                }
                return implode('', $chunk_data);
            }
        }
        // Data has not been splitted to chunks on save
        return $data;
    }
    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param string $id Cache id
     * @return int|false "last modified" timestamp (int) or false if not available
     */
    public function test($id)
    {
        $result = $this->memcached->get($this->prefix . $id);
        if ($result === false) {
            return false;
        }
        // Return current timestamp as memcached doesn't store mtime
        return time();
    }
    /**
     * Save some string data into a cache record
     *
     * @param mixed $data Data to cache
     * @param string $id Cache id
     * @param array $tags Array of strings, the cache record will be tagged by each string entry
     * @param int|null $specificLifetime If not null, set a specific lifetime for this cache record
     * @return bool True if no problem
     */
    public function save($data, $id, $tags = [], $specific_lifetime = null)
    {
        // Handle chunking for large data
        if (is_string($data) && strlen($data) > $this->_options['slab_size']) {
            $data_chunks = str_split($data, $this->_options['slab_size']);
            for ($i = 0, $count = count($data_chunks); $i < $count; $i++) {
                $chunk_id = $this->get_chunk_id($id, $i);
                if (!$this->save_direct($data_chunks[$i], $chunk_id, $tags, $specific_lifetime)) {
                    $this->clean_the_mess($id, $i + 1);
                    return false;
                }
            }
            $data = self::CODE_WORD . '|' . $i;
        }
        return $this->save_direct($data, $id, $tags, $specific_lifetime);
    }
    /**
     * Remove a cache record
     *
     * @param string $id Cache id
     * @return bool True if no problem
     */
    public function remove($id)
    {
        return $this->memcached->delete($this->prefix . $id);
    }
    /**
     * Clean some cache records
     *
     * Available modes:
     * - all: remove all cache entries
     * - old: not supported by memcached
     * - matchingTag: not supported by memcached
     * - notMatchingTag: not supported by memcached
     * - matchingAnyTag: not supported by memcached
     *
     * @param string $mode Clean mode
     * @param array $tags Array of tags
     * @return bool True if no problem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, $tags = [])
    {
        switch ($mode) {
            case Cache_Constants::CLEANING_MODE_ALL:
            case 'all':
                return $this->memcached->flush();
            case Cache_Constants::CLEANING_MODE_OLD:
            case 'old':
                // Memcached handles expiration automatically
                return true;
            case Cache_Constants::CLEANING_MODE_MATCHING_TAG:
            case Cache_Constants::CLEANING_MODE_NOT_MATCHING_TAG:
            case Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG:
            case 'matchingTag':
            case 'notMatchingTag':
            case 'matchingAnyTag':
                // Memcached doesn't support tags natively
                $this->log('Memcached backend does not support tag-based cleaning');
                return false;
            default:
                throw new Cache_Exception(__('Invalid mode for clean() method'));
        }
    }
    /**
     * Return an array of stored cache ids
     *
     * @return array Array of stored cache ids (string)
     */
    public function get_ids()
    {
        // Memcached doesn't support listing all keys
        $this->log('Memcached backend does not support listing all keys');
        return [];
    }
    /**
     * Return an array of stored tags
     *
     * @return array Array of stored tags (string)
     */
    public function get_tags()
    {
        // Memcached doesn't support tags natively
        return [];
    }
    /**
     * Return an array of stored cache ids which match given tags
     *
     * @param array $tags Array of tags
     * @return array Array of matching cache ids (string)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_ids_matching_tags($tags = [])
    {
        // Memcached doesn't support tags natively
        return [];
    }
    /**
     * Return an array of stored cache ids which don't match given tags
     *
     * @param array $tags Array of tags
     * @return array Array of not matching cache ids (string)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_ids_not_matching_tags($tags = [])
    {
        // Memcached doesn't support tags natively
        return [];
    }
    /**
     * Return an array of stored cache ids which match any given tags
     *
     * @param array $tags Array of tags
     * @return array Array of any matching cache ids (string)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_ids_matching_any_tags($tags = [])
    {
        // Memcached doesn't support tags natively
        return [];
    }
    /**
     * Return the filling percentage of the backend storage
     *
     * @return int Integer between 0 and 100
     */
    public function get_filling_percentage()
    {
        $stats = $this->memcached->get_stats();
        if (empty($stats)) {
            return 0;
        }
        $total = 0;
        $used = 0;
        foreach ($stats as $stat) {
            if (isset($stat['limit_maxbytes']) && isset($stat['bytes'])) {
                $total += $stat['limit_maxbytes'];
                $used += $stat['bytes'];
            }
        }
        if ($total == 0) {
            return 0;
        }
        return (int) (100 * ($used / $total));
    }
    /**
     * Return an array of metadatas for the given cache id
     *
     * @param string $id Cache id
     * @return array|false Array of metadatas or false if not found
     */
    public function get_metadatas($id)
    {
        $result = $this->memcached->get($this->prefix . $id);
        if ($result === false) {
            return false;
        }
        // Memcached doesn't store detailed metadata
        return [
            'expire' => time() + 86400,
            // Default assumption
            'tags' => [],
            'mtime' => time(),
        ];
    }
    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id Cache id
     * @param int $extraLifetime Extra lifetime in seconds
     * @return bool True if ok
     */
    public function touch($id, $extra_lifetime)
    {
        $data = $this->memcached->get($this->prefix . $id);
        if ($data === false) {
            return false;
        }
        // Re-save with extended lifetime
        return $this->memcached->set($this->prefix . $id, $data, $extra_lifetime);
    }
    /**
     * Return an associative array of capabilities (booleans) of the backend
     *
     * @return array Associative array of capabilities
     */
    public function get_capabilities()
    {
        return ['automatic_cleaning' => true, 'tags' => false, 'expired_read' => false, 'priority' => false, 'infinite_lifetime' => false, 'get_list' => false];
    }
    /**
     * Load data directly from memcached (without chunking logic)
     *
     * @param string $id Cache id
     * @param bool $doNotTestCacheValidity If true, cache validity is not tested
     * @return string|false Cached data or false
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function load_direct(string $id, bool $do_not_test_cache_validity = false)
    {
        $result = $this->memcached->get($this->prefix . $id);
        return $result === false ? false : $result;
    }
    /**
     * Save data directly to memcached (without chunking logic)
     *
     * @param mixed $data Data to cache
     * @param string $id Cache id
     * @param array $tags Array of tags (ignored by memcached)
     * @param int|null $specificLifetime Lifetime in seconds
     * @return bool True if no problem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function save_direct($data, string $id, array $tags = [], ?int $specific_lifetime = null): bool
    {
        $lifetime = $this->get_lifetime($specific_lifetime);
        return $this->memcached->set($this->prefix . $id, $data, $lifetime);
    }
    /**
     * Returns ID of a specific chunk on the basis of data's ID
     *
     * @param string $id Main data's ID
     * @param int $index Particular chunk number to return ID for
     * @return string
     */
    private function get_chunk_id(string $id, int $index): string
    {
        return "{$id}[{$index}]";
    }
    /**
     * Remove saved chunks in case something went wrong
     *
     * @param string $id ID of data's info cell
     * @param int $chunks Number of chunks to remove
     * @return void
     */
    private function clean_the_mess(string $id, int $chunks): void
    {
        for ($i = 0; $i < $chunks; $i++) {
            $this->remove($this->get_chunk_id($id, $i));
        }
        $this->remove($id);
    }
    /**
     * Log a message
     *
     * @param string $message
     * @param int $priority
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function log(string $message, int $priority = 4): void
    {
        if (isset($this->_options['logging']) && $this->_options['logging']) {
            error_log($message);
        }
    }
}