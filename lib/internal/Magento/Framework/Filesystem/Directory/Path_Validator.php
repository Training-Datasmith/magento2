<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\Validator_Exception;
use Magento\Framework\Filesystem\Driver_Interface;
use Magento\Framework\Phrase;
/**
 * @inheritDoc
 *
 * Validates paths using driver.
 */
class Path_Validator implements Path_Validator_Interface
{
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
        if (!$absolute_path) {
            $actual_path = $this->driver->get_real_path_safety($this->driver->get_absolute_path($real_directory_path . DIRECTORY_SEPARATOR, $path, $scheme));
        } else {
            $actual_path = $this->driver->get_real_path_safety($path);
        }
        if (preg_match('/(?:^-|\s-\S|[\t\r\n\f])/', $path) || mb_strpos($actual_path, $real_directory_path) !== 0 && rtrim($path, DIRECTORY_SEPARATOR) !== $real_directory_path) {
            throw new Validator_Exception(new Phrase('Path "%1" cannot be used with directory "%2"', [$path, $directory_path]));
        }
    }
}