<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\File;

use Laminas\Validator\Validator_Interface;
/**
 * Interface HttpInterface
 *
 * Provides methods for validating and handling HTTP file uploads.
 */
interface Http_Interface
{
    /**
     * Validates the uploaded files.
     *
     * @param mixed $files
     * @return bool
     */
    public function is_valid($files = null): bool;
    /**
     * Retrieves the list of errors encountered during validation.
     *
     * @return array
     */
    public function get_errors(): array;
    /**
     * Adds a validator to the validation chain.
     *
     * @param string|ValidatorInterface $validator
     * @return HttpInterface
     */
    public function add_validator(string|Validator_Interface $validator): Http_Interface;
    /**
     * Checks if the files have been uploaded.
     *
     * @param mixed $files
     * @return bool
     */
    public function is_uploaded($files = null): bool;
    /**
     * Retrieve additional internal file information for files
     *
     * @param  string $file (Optional) File to get information for
     * @return mixed
     */
    public function get_file_info($file = null): mixed;
}