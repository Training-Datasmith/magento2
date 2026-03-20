<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Generator;

use Magento\Framework\Exception\File_System_Exception;
/**
 * Manages generated code.
 */
class Io
{
    /**
     * Default code generation directory
     * Should correspond the value from \Magento\Framework\Filesystem
     */
    public const DEFAULT_DIRECTORY = 'generated/code';
    /**
     * Path to directory where new file must be created
     *
     * @var string
     */
    private $_generation_directory;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $filesystem_driver;
    /**
     * @param \Magento\Framework\Filesystem\Driver\File $filesystemDriver
     * @param null|string $generationDirectory
     */
    public function __construct(\Magento\Framework\Filesystem\Driver\File $filesystem_driver, $generation_directory = null)
    {
        $this->filesystem_driver = $filesystem_driver;
        $this->init_generator_directory($generation_directory);
    }
    /**
     * Get path to generation directory
     *
     * @param null|string $directory
     * @return string
     */
    protected function init_generator_directory($directory = null)
    {
        if ($directory) {
            $this->_generation_directory = rtrim($directory, '/') . '/';
        } else {
            $this->_generation_directory = realpath(__DIR__ . '/../../../../') . '/' . self::DEFAULT_DIRECTORY . '/';
        }
    }
    /**
     * @param string $className
     * @return string
     */
    public function get_result_file_directory($class_name)
    {
        $file_name = $this->generate_result_file_name($class_name);
        $path_parts = explode('/', $file_name);
        unset($path_parts[count($path_parts) - 1]);
        return implode('/', $path_parts) . '/';
    }
    /**
     * @param string $className
     * @return string
     */
    public function generate_result_file_name($class_name)
    {
        return $this->_generation_directory . ltrim(str_replace(['\\', '_'], '/', $class_name), '/') . '.php';
    }
    /**
     * @param string $fileName
     * @param string $content
     * @throws FileSystemException
     * @return bool
     */
    public function write_result_file($file_name, $content)
    {
        /**
         * Rename is atomic on *nix systems, while file_put_contents is not. Writing to a
         * temporary file whose name is process-unique and renaming to the real location helps
         * avoid race conditions. Race condition can occur if the compiler has not been run, when
         * multiple processes are attempting to access the generated file simultaneously.
         */
        $content = "<?php\n" . $content;
        $tmp_file = $file_name . '.' . getmypid();
        $this->filesystem_driver->file_put_contents($tmp_file, $content);
        try {
            $success = $this->filesystem_driver->rename($tmp_file, $file_name);
        } catch (File_System_Exception $e) {
            if (!$this->file_exists($file_name)) {
                throw $e;
            } else {
                /**
                 * Due to race conditions, file may have already been written, causing rename to fail. As long as
                 * the file exists, everything is okay.
                 */
                $success = true;
            }
        }
        return $success;
    }
    /**
     * @return bool
     */
    public function make_generation_directory()
    {
        return $this->_make_directory($this->_generation_directory);
    }
    /**
     * @param string $className
     * @return bool
     */
    public function make_result_file_directory($class_name)
    {
        return $this->_make_directory($this->get_result_file_directory($class_name));
    }
    /**
     * @return string
     */
    public function get_generation_directory()
    {
        return $this->_generation_directory;
    }
    /**
     * @param string $fileName
     * @return bool
     */
    public function file_exists($file_name)
    {
        return $this->filesystem_driver->is_exists($file_name);
    }
    /**
     * Wrapper for include
     *
     * @param string $fileName
     * @return mixed
     * @codeCoverageIgnore
     */
    public function include_file($file_name)
    {
        return include $file_name;
    }
    /**
     * @param string $directory
     * @return bool
     */
    private function _make_directory($directory)
    {
        if ($this->filesystem_driver->is_writable($directory)) {
            return true;
        }
        try {
            if (!$this->filesystem_driver->is_directory($directory)) {
                $this->filesystem_driver->create_directory($directory);
            }
            return true;
        } catch (File_System_Exception $e) {
            return false;
        }
    }
}