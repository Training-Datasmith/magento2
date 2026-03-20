<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\Validator_Exception;
/**
 * Validate paths to be used with directories.
 *
 * @api
 */
interface Path_Validator_Interface
{
    /**
     * Validate if path can be used with a directory.
     *
     * @param string $directoryPath
     * @param string $path
     * @param string|null $scheme
     * @param bool $absolutePath Is given path an absolute path?.
     * @throws ValidatorException
     *
     * @return void
     */
    public function validate(string $directory_path, string $path, ?string $scheme = null, bool $absolute_path = false): void;
}