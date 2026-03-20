<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\Validator_Exception;
use Magento\Framework\Filesystem\Driver_Interface;
use Magento\Framework\Phrase;
/**
 * Validates paths using driver.
 */
class Deny_List_Path_Validator implements Path_Validator_Interface
{
    /**
     * File deny list using regular expressions
     *
     * @var string[]
     */
    private $file_deny_list = ['htaccess'];
    /**
     * Deny list exception list
     *
     * @var string[]
     */
    private $exception_list = [];
    /**
     * @var DriverInterface
     */
    private $driver;
    /**
     * @param DriverInterface $driver
     */
    public function __construct(Driver_Interface $driver)
    {
        $this->driver = $driver;
    }
    /**
     * @inheritDoc
     */
    public function validate(string $directory_path, string $path, ?string $scheme = null, bool $absolute_path = false): void
    {
        $real_directory_path = $this->driver->get_real_path_safety($directory_path);
        $full_path = $this->driver->get_absolute_path(rtrim($real_directory_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $path, $scheme);
        if (!$absolute_path) {
            $actual_path = $this->driver->get_real_path_safety($full_path);
        } else {
            $actual_path = $this->driver->get_real_path_safety($path);
        }
        if (in_array($full_path, $this->exception_list, true)) {
            return;
        }
        foreach ($this->file_deny_list as $file) {
            $base_name = pathinfo($actual_path, PATHINFO_BASENAME);
            if (strpos($base_name, $file) !== false || preg_match('#' . "\\." . $file . '#', $full_path)) {
                throw new Validator_Exception(new Phrase('"%1" is not a valid file path', [$path]));
            }
        }
    }
    /**
     * Allow addition of new exceptions given full path
     *
     * @param string $fullPath
     */
    public function add_exception(string $full_path)
    {
        if (!in_array($full_path, $this->exception_list)) {
            array_push($this->exception_list, $full_path);
        }
    }
    /**
     * Allow addition of new exceptions given full path
     *
     * @param string $fullPath
     */
    public function remove_exception(string $full_path)
    {
        if (($key = array_search($full_path, $this->exception_list)) !== false) {
            unset($this->exception_list[$key]);
        }
    }
}