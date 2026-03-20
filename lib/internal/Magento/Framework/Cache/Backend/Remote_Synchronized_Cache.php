<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Backend;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Cache\Composite_Stale_Cache_Notifier;
use Magento\Framework\Cache\Exception\Cache_Exception;
use Magento\Framework\Cache\Stale_Cache_Notifier_Interface;
/**
 * Remote synchronized cache
 *
 * This class created for correct work with local caches and multiple web nodes,
 * in order to be sure that we always have up to date local version of cache.
 * This class will check cache version from remote cache and in case it's newer
 * than local one, it will update local one from remote cache (two-level cache).
 */
class Remote_Synchronized_Cache extends Abstract_Backend implements Extended_Backend_Interface
{
    /**
     * Local backend cache adapter
     *
     * @var ExtendedBackendInterface
     */
    private $local;
    /**
     * Remote backend cache adapter
     *
     * @var ExtendedBackendInterface
     */
    private $remote;
    /**
     * Suffix for hash to compare data version in cache storage.
     */
    private const HASH_SUFFIX = ':hash';
    /**
     * Prefix for locks in case stale cache is used.
     */
    private const REMOTE_SYNC_LOCK_PREFIX = 'rsl::';
    /**
     *  Available options
     *
     * @var array available options
     */
    protected $_options = ['remote_backend' => '', 'remote_backend_custom_naming' => true, 'remote_backend_autoload' => true, 'remote_backend_options' => [], 'local_backend' => '', 'local_backend_options' => [], 'local_backend_custom_naming' => true, 'local_backend_autoload' => true, 'use_stale_cache' => false, 'cleanup_percentage' => 95];
    /**
     * In memory state for locks.
     *
     * @var array
     */
    private $lock_list = [];
    /**
     * Sign for locks, helps to avoid removing a lock that was created by another client
     *
     * @var string
     */
    private $lock_sign;
    /**
     * @var StaleCacheNotifierInterface
     */
    private $notifier;
    /**
     * Constructor
     *
     * @param array $options
     * @throws CacheException
     */
    public function __construct($options = [])
    {
        parent::__construct($options);
        // Validate and set remote backend
        if ($this->_options['remote_backend'] === null) {
            throw new Cache_Exception(__('remote_backend option must be set'));
        }
        if (!$this->_options['remote_backend'] instanceof Extended_Backend_Interface) {
            throw new Cache_Exception(__('remote_backend must implement ExtendedBackendInterface'));
        }
        $this->remote = $this->_options['remote_backend'];
        // Validate and set local backend
        if ($this->_options['local_backend'] === null) {
            throw new Cache_Exception(__('local_backend option must be set'));
        }
        if (!$this->_options['local_backend'] instanceof Extended_Backend_Interface) {
            throw new Cache_Exception(__('local_backend must implement ExtendedBackendInterface'));
        }
        $this->local = $this->_options['local_backend'];
        $this->lock_sign = $this->generate_lock_sign();
    }
    /**
     * @inheritDoc
     */
    public function set_directives($directives)
    {
        $this->remote->set_directives($directives);
        $this->local->set_directives($directives);
    }
    /**
     * Return hash sign of the data.
     *
     * @param string $data
     * @return string
     */
    private function get_data_version(string $data)
    {
        return \hash('sha256', $data);
    }
    /**
     * Load data version by id from remote.
     *
     * @param string $id
     * @return false|string
     */
    private function load_remote_data_version(string $id)
    {
        return $this->remote->load($id . self::HASH_SUFFIX);
    }
    /**
     * Save new data version to remote.
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param mixed $specificLifetime
     * @return bool
     */
    private function save_remote_data_version(string $data, string $id, array $tags, $specific_lifetime = false)
    {
        return $this->remote->save($this->get_data_version($data), $id . self::HASH_SUFFIX, $tags, $specific_lifetime);
    }
    /**
     * Remove remote data version.
     *
     * @param string $id
     * @return bool
     */
    private function remove_remote_data_version($id)
    {
        return $this->remote->remove($id . self::HASH_SUFFIX);
    }
    /**
     * @inheritdoc
     */
    public function load($id, $do_not_test_cache_validity = false)
    {
        $local_data = $this->local->load($id);
        if ($local_data !== false) {
            if ($this->get_data_version($local_data) === $this->load_remote_data_version($id)) {
                return $local_data;
            }
        }
        $remote_data = $this->remote->load($id);
        if ($remote_data !== false) {
            $this->local->save($remote_data, $id);
            return $remote_data;
        } elseif ($local_data && $this->_options['use_stale_cache']) {
            if ($this->lock($id)) {
                return false;
            } else {
                $this->notify_stale_cache();
                return $local_data;
            }
        }
        return false;
    }
    /**
     * @inheritdoc
     */
    public function test($id)
    {
        return $this->_options['use_stale_cache'] ? $this->local->test($id) ?? $this->remote->test($id) : $this->remote->test($id);
    }
    /**
     * @inheritdoc
     */
    public function save($data, $id, $tags = [], $specific_lifetime = null)
    {
        $data_to_save = $data;
        $rem_hash = $this->load_remote_data_version($id);
        $is_remote_up_to_date = false;
        if ($rem_hash !== false && $this->get_data_version($data) === $rem_hash) {
            $remote_data = $this->remote->load($id);
            if ($remote_data !== false && $this->get_data_version($data) === $this->get_data_version($remote_data)) {
                $is_remote_up_to_date = true;
                $data_to_save = $remote_data;
            }
        }
        if (!$is_remote_up_to_date) {
            $this->remote->save($data, $id, $tags, $specific_lifetime);
            $this->save_remote_data_version($data, $id, $tags, $specific_lifetime);
        }
        if ($this->_options['use_stale_cache']) {
            $this->unlock($id);
        }
        // mt_rand() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        if (!mt_rand(0, 100) && $this->check_if_local_cache_space_exceeded()) {
            $this->local->clean();
        }
        // Local cache doesn't save tags intentionally since it will cause inconsistency after flushing the cache
        // in multinode environment
        return $this->local->save($data_to_save, $id, [], $specific_lifetime);
    }
    /**
     * Check if local cache space bigger that configure amount
     *
     * @return bool
     */
    private function check_if_local_cache_space_exceeded()
    {
        return $this->local->get_filling_percentage() >= ($this->_options['cleanup_percentage'] ?? 95);
    }
    /**
     * @inheritdoc
     */
    public function remove($id)
    {
        $result = $this->remove_remote_data_version($id) && $this->remote->remove($id);
        if ($result && !$this->_options['use_stale_cache']) {
            $result = $this->local->remove($id);
        }
        return $result;
    }
    /**
     * @inheritdoc
     */
    public function clean($mode = 'all', $tags = [])
    {
        return $this->remote->clean($mode, $tags) && $this->local->clean($mode);
    }
    /**
     * @inheritdoc
     */
    public function get_ids()
    {
        return $this->remote->get_ids();
    }
    /**
     * @inheritdoc
     */
    public function get_tags()
    {
        return $this->remote->get_tags();
    }
    /**
     * @inheritdoc
     */
    public function get_ids_matching_tags($tags = [])
    {
        return $this->remote->get_ids_matching_tags($tags);
    }
    /**
     * @inheritdoc
     */
    public function get_ids_not_matching_tags($tags = [])
    {
        return $this->remote->get_ids_not_matching_tags($tags);
    }
    /**
     * @inheritdoc
     */
    public function get_ids_matching_any_tags($tags = [])
    {
        return $this->remote->get_ids_matching_any_tags($tags);
    }
    /**
     * @inheritdoc
     */
    public function get_filling_percentage()
    {
        return $this->remote->get_filling_percentage();
    }
    /**
     * @inheritdoc
     */
    public function get_metadatas($id)
    {
        return $this->remote->get_metadatas($id);
    }
    /**
     * @inheritdoc
     */
    public function touch($id, $extra_lifetime)
    {
        return $this->remote->touch($id, $extra_lifetime);
    }
    /**
     * @inheritdoc
     */
    public function get_capabilities()
    {
        return $this->remote->get_capabilities();
    }
    /**
     * Sets a lock
     *
     * @param string $id
     * @return bool
     */
    private function lock(string $id): bool
    {
        $this->lock_list[$id] = microtime(true);
        $data = $this->remote->load($this->get_lock_name($id));
        if (false !== $data) {
            return false;
        }
        $this->remote->save($this->lock_sign, $this->get_lock_name($id), [], 10);
        $data = $this->remote->load($this->get_lock_name($id));
        if ($data === $this->lock_sign) {
            return true;
        }
        return false;
    }
    /**
     * Release a lock.
     *
     * @param string|int $id
     * @return bool
     */
    private function unlock(string|int $id): bool
    {
        $id = (string) $id;
        if (isset($this->lock_list[$id])) {
            unset($this->lock_list[$id]);
        }
        $data = $this->remote->load($this->get_lock_name($id));
        if (false === $data) {
            return false;
        }
        $remove_result = false;
        if ($data === $this->lock_sign) {
            $remove_result = (bool) $this->remote->remove($this->get_lock_name($id));
        }
        return $remove_result;
    }
    /**
     * Calculate lock name.
     *
     * @param string $id
     * @return string
     */
    private function get_lock_name(string $id): string
    {
        return self::REMOTE_SYNC_LOCK_PREFIX . $id;
    }
    /**
     * Release all locks.
     *
     * @return void
     */
    private function unlock_all(): void
    {
        foreach (array_keys($this->lock_list) as $id) {
            $this->unlock($id);
        }
    }
    /**
     * Release all locks on destruct.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->unlock_all();
    }
    /**
     * Function that generates lock sign that helps to avoid removing a lock that was created by another client.
     *
     * @return string
     */
    private function generate_lock_sign()
    {
        $sign = \implode('-', [\getmypid(), \crc32(\gethostname())]);
        try {
            $sign .= '-' . \bin2hex(\random_bytes(4));
        } catch (\Exception $e) {
            $sign .= '-' . \uniqid('-uniqid-');
        }
        return $sign;
    }
    /**
     * Function that notifies configured cache types to be switched off.
     */
    private function notify_stale_cache(): void
    {
        $this->notifier = $this->notifier ?? Object_Manager::get_instance()->get(Composite_Stale_Cache_Notifier::class);
        $this->notifier->cache_loader_is_using_stale_cache();
    }
}