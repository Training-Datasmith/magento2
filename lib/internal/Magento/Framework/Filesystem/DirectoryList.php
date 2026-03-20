<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Filesystem;

/**
 * Application file system directories dictionary.
 *
 * Provides information about what directories are available in the application.
 * Serves as a customization point to specify different directories or add your own.
 *
 * A list of directories
 *
 * Each list item consists of:
 * - a directory path in the filesystem
 * - optionally, a URL path
 *
 * This object is intended to be immutable (a "value object").
 * The defaults are pre-defined and can be modified only by inheritors of this class.
 * Through the constructor, it is possible to inject custom paths or URL paths, but impossible to inject new types.
 *
 * @api
 */
class Directory_List
{
    /**#@+
     * Keys of directory configuration
     */
    public const PATH = 'path';
    public const URL_PATH = 'uri';
    /**#@- */
    /**
     * System base temporary directory
     */
    public const SYS_TMP = 'sys_tmp';
    /**
     * Root path
     *
     * @var string
     */
    private $root;
    /**
     * Directories configurations
     *
     * @var array
     */
    private $directories;
    /**
     * Predefined types/paths
     *
     * @return array
     */
    public static function get_default_config()
    {
        return [self::SYS_TMP => [self::PATH => '']];
    }
    /**
     * Validates format and contents of given configuration
     *
     * @param array $config
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function validate($config)
    {
        if (!is_array($config)) {
            throw new \InvalidArgumentException('Unexpected value type.');
        }
        $default_config = static::get_default_config();
        foreach ($config as $type => $row) {
            if (!is_array($row)) {
                throw new \InvalidArgumentException('Unexpected value type.');
            }
            if (!isset($default_config[$type])) {
                throw new \InvalidArgumentException("Unknown type: {$type}");
            }
            if (!isset($row[self::PATH]) && !isset($row[self::URL_PATH])) {
                throw new \InvalidArgumentException("Missing required keys at: {$type}");
            }
        }
    }
    /**
     * Constructor
     *
     * @param string $root
     * @param array $config
     */
    public function __construct($root, array $config = [])
    {
        static::validate($config);
        $this->root = $this->normalize_path($root);
        $this->directories = static::get_default_config();
        $sys_tmp_path = get_cfg_var('upload_tmp_dir') ?: sys_get_temp_dir();
        $this->directories[self::SYS_TMP] = [self::PATH => realpath($sys_tmp_path)];
        // inject custom values from constructor
        foreach ($this->directories as $code => $dir) {
            foreach ([self::PATH, self::URL_PATH] as $key) {
                if (isset($config[$code][$key])) {
                    $this->directories[$code][$key] = $config[$code][$key];
                }
            }
        }
        // filter/validate values
        foreach ($this->directories as $code => $dir) {
            $path = $this->normalize_path($dir[self::PATH]);
            if (!$this->is_absolute($path)) {
                $path = $this->prepend_root($path);
            }
            $this->directories[$code][self::PATH] = $path;
            if (isset($dir[self::URL_PATH])) {
                $this->assert_url_path($dir[self::URL_PATH]);
            }
        }
    }
    /**
     * Converts slashes in path to a conventional unix-style
     *
     * @param string $path
     * @return string
     */
    private function normalize_path($path)
    {
        return $path !== null ? str_replace('\\', '/', $path) : '';
    }
    /**
     * Validates a URL path
     *
     * Path must be usable as a fragment of a URL path.
     * For interoperability and security purposes, no uppercase or "upper directory" paths like "." or ".."
     *
     * @param string $urlPath
     * @return void
     * @throws \InvalidArgumentException
     */
    private function assert_url_path($url_path)
    {
        if (!preg_match('/^([a-z0-9_]+[a-z0-9\._]*(\/[a-z0-9_]+[a-z0-9\._]*)*)?$/', $url_path)) {
            throw new \InvalidArgumentException("URL path must be relative directory path in lowercase with '/' directory separator: '{$url_path}'");
        }
    }
    /**
     * Concatenates root directory path with a relative path
     *
     * @param string $path
     * @return string
     */
    protected function prepend_root($path)
    {
        $root = $this->get_root();
        return $root . ($root && $path ? '/' : '') . $path;
    }
    /**
     * Determine if a path is absolute
     *
     * @param string $path
     * @return bool
     */
    protected function is_absolute($path)
    {
        $path = $path !== null ? strtr($path, '\\', '/') : '';
        if (strpos($path, '/') === 0) {
            //is UnixRoot
            return true;
        } elseif (preg_match('#^\w{1}:/#', $path)) {
            //is WindowsRoot
            return true;
        } elseif (parse_url($path, PHP_URL_SCHEME) !== null) {
            //is WindowsLetter
            return true;
        }
        return false;
    }
    /**
     * Gets a filesystem path of the root directory
     *
     * @return string
     */
    public function get_root()
    {
        return $this->root;
    }
    /**
     * Gets a filesystem path of a directory
     *
     * @param string $code
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function get_path($code)
    {
        $this->assert_code($code);
        return $this->directories[$code][self::PATH];
    }
    /**
     * Gets URL path of a directory
     *
     * @param string $code
     * @return string|bool
     */
    public function get_url_path($code)
    {
        $this->assert_code($code);
        if (!isset($this->directories[$code][self::URL_PATH])) {
            return false;
        }
        return $this->directories[$code][self::URL_PATH];
    }
    /**
     * Asserts that specified directory code is in the registry
     *
     * @param string $code
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return void
     */
    private function assert_code($code)
    {
        if (!isset($this->directories[$code])) {
            throw new \Magento\Framework\Exception\File_System_Exception(new \Magento\Framework\Phrase('Unknown directory type: \'%1\'', [$code]));
        }
    }
    /**
     * Disable show ObjectManager internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}