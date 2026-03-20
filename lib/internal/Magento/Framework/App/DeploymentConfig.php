<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\Config\Config_Options_List_Constants;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Phrase;
/**
 * Application deployment configuration
 *
 * @api
 * @since 100.0.2
 */
class Deployment_Config
{
    private const MAGENTO_ENV_PREFIX = 'MAGENTO_DC_';
    private const ENV_NAME_PATTERN = '~^#env\(\s*(?<name>\w+)\s*(,\s*"(?<default>[^"]+)")?\)$~';
    private const OVERRIDE_KEY = self::MAGENTO_ENV_PREFIX . '_OVERRIDE';
    /**
     * Configuration reader
     *
     * @var DeploymentConfig\Reader
     */
    private $reader;
    /**
     * Configuration data
     *
     * @var array
     */
    private $data = [];
    /**
     * Flattened data
     *
     * @var array
     */
    private $flat_data = [];
    /**
     * Injected configuration data
     *
     * @var array
     */
    private $override_data;
    /**
     * @var array
     */
    private $env_overrides = [];
    /**
     * @var array
     */
    private $reader_load = [];
    /**
     * Constructor
     *
     * Data can be optionally injected in the constructor. This object's public interface is intentionally immutable
     *
     * @param DeploymentConfig\Reader $reader
     * @param array $overrideData
     */
    public function __construct(Deployment_Config\Reader $reader, $override_data = [])
    {
        $this->reader = $reader;
        $this->override_data = $override_data;
    }
    /**
     * Gets data from flattened data
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function get($key = null, $default_value = null)
    {
        if ($key === null) {
            if (empty($this->flat_data)) {
                $this->reload_data();
            }
            return $this->flat_data;
        }
        $result = $this->get_by_key($key);
        if ($result === null) {
            if (empty($this->flat_data) || count($this->get_all_env_overrides())) {
                $this->reload_data();
            }
            $result = $this->get_by_key($key);
        }
        return $result ?? $default_value;
    }
    /**
     * Checks if data available
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function is_available()
    {
        return $this->get(Config_Options_List_Constants::CONFIG_PATH_INSTALL_DATE) !== null;
    }
    /**
     * Gets a value specified key from config data
     *
     * @param string|null $key
     * @return null|mixed
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function get_config_data($key = null)
    {
        if ($key === null) {
            if (empty($this->data)) {
                $this->reload_initial_data();
            }
            return $this->data;
        }
        $result = $this->get_config_data_by_key($key);
        if ($result === null) {
            $this->reload_initial_data();
            $result = $this->get_config_data_by_key($key);
        }
        return $result;
    }
    /**
     * Resets config data
     *
     * @return void
     */
    public function reset_data()
    {
        $this->data = [];
        $this->flat_data = [];
    }
    /**
     * Check if data from deploy files is available
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     * @since 100.1.3
     */
    public function is_db_available()
    {
        return $this->get_config_data('db') !== null;
    }
    /**
     * Get additional configuration from env variable MAGENTO_DC__OVERRIDE
     *
     * Data should be JSON encoded
     *
     * @return array
     */
    private function get_env_override(): array
    {
        $env = getenv(self::OVERRIDE_KEY);
        return !empty($env) ? json_decode($env, true) ?? [] : [];
    }
    /**
     * Loads the configuration data
     *
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function reload_initial_data(): void
    {
        if (empty($this->reader_load) || empty($this->data) || empty($this->flat_data)) {
            $this->reader_load = $this->reader->load();
        }
        $this->data = array_replace($this->reader_load, $this->override_data ?? [], $this->get_env_override());
    }
    /**
     * Loads the configuration data
     *
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function reload_data(): void
    {
        $this->reload_initial_data();
        // flatten data for config retrieval using get()
        $this->flat_data = $this->flatten_params($this->data);
        $this->flat_data = $this->get_all_env_overrides() + $this->flat_data;
    }
    /**
     * Load all getenv() configs once
     *
     * @return array
     */
    private function get_all_env_overrides(): array
    {
        if (empty($this->env_overrides)) {
            // allow reading values from env variables by convention
            // MAGENTO_DC_{path}, like db/connection/default/host =>
            // can be overwritten by MAGENTO_DC_DB__CONNECTION__DEFAULT__HOST
            foreach (getenv() as $key => $value) {
                if (false !== \strpos($key, self::MAGENTO_ENV_PREFIX) && $key !== self::OVERRIDE_KEY) {
                    // convert MAGENTO_DC_DB__CONNECTION__DEFAULT__HOST into db/connection/default/host
                    $flat_key = strtolower(str_replace([self::MAGENTO_ENV_PREFIX, '__'], ['', '/'], $key));
                    $this->env_overrides[$flat_key] = match ($value) {
                        'true', 'TRUE' => true,
                        'false', 'FALSE' => false,
                        default => $value,
                    };
                }
            }
        }
        return $this->env_overrides;
    }
    /**
     * Array keys conversion
     *
     * Convert associative array of arbitrary depth to a flat associative array with concatenated key path as keys
     * each level of array is accessible by path key
     *
     * @param array $params
     * @param string|null $path
     * @param array|null $flattenResult
     * @return array
     * @throws RuntimeException
     */
    private function flatten_params(array $params, ?string $path = null, ?array &$flatten_result = null): array
    {
        if (null === $flatten_result) {
            $flatten_result = [];
        }
        foreach ($params as $key => $param) {
            if ($path) {
                $new_path = $path . '/' . $key;
            } else {
                $new_path = $key;
            }
            if (isset($flatten_result[$new_path])) {
                //phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new RuntimeException(new Phrase("Key collision '%1' is already defined.", [$new_path]));
            }
            if (is_array($param)) {
                $flatten_result[$new_path] = $param;
                $this->flatten_params($param, $new_path, $flatten_result);
            } else {
                // allow reading values from env variables
                // value need to be specified in %env(NAME, "default value")% format
                // like #env(DB_PASSWORD), #env(DB_NAME, "test")
                if ($param !== null && preg_match(self::ENV_NAME_PATTERN, $param, $matches)) {
                    $param = getenv($matches['name']) ?: $matches['default'] ?? null;
                }
                $flatten_result[$new_path] = $param;
            }
        }
        return $flatten_result;
    }
    /**
     * Returns flat data by key
     *
     * @param string|null $key
     * @return mixed|null
     */
    private function get_by_key(?string $key)
    {
        if (array_key_exists($key, $this->flat_data) && $this->flat_data[$key] === null) {
            return '';
        }
        return $this->flat_data[$key] ?? null;
    }
    /**
     * Returns data by key
     *
     * @param string|null $key
     * @return mixed|null
     */
    private function get_config_data_by_key(?string $key)
    {
        return $this->data[$key] ?? null;
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