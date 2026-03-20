<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\File;

use Laminas\Validator\File\Upload;
use Laminas\Validator\Validator_Interface;
use Magento\Framework\Exception\Input_Exception;
class Http implements Http_Interface
{
    /**
     * Internal list of validators
     * @var array
     */
    protected $validators = [];
    /**
     * Internal list of files
     * @var array
     */
    protected $files = [];
    /**
     * TMP directory
     * @var string
     */
    protected $tmp_dir;
    /**
     * Available options for file transfers
     * @var array
     */
    protected $options = ['ignoreNoFile' => false, 'useByteString' => true, 'magicFile' => null, 'detectInfos' => true];
    /**
     *
     * @var array
     */
    protected $messages = [];
    /**
     * Constructor for Http File Transfers
     *
     * @param array $options
     * @throws InputException
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        $this->prepare_files();
        $this->add_validator(new Upload());
    }
    /**
     * Checks if the files are valid
     *
     * @param  string|array $files (Optional) Files to check
     * @return bool True if all checks are valid
     */
    public function is_valid($files = null): bool
    {
        $file_content = $this->get_file_info($files);
        $valid = true;
        foreach ($file_content as $file) {
            foreach ($this->validators as $validator) {
                if (!$validator->is_valid($file['tmp_name'], $file)) {
                    $valid = false;
                    $this->messages += $validator->get_messages();
                }
            }
        }
        return $valid;
    }
    /**
     * Prepare the $_FILES array to match the internal syntax of one file per entry
     *
     * @return HttpInterface
     */
    protected function prepare_files(): Http_Interface
    {
        $this->files = [];
        $options = $this->options;
        foreach ($_FILES as $form => $content) {
            $content['options'] = $options;
            $content['validated'] = false;
            $content['received'] = false;
            $content['filtered'] = false;
            $this->files[$form] = $content;
        }
        return $this;
    }
    /**
     * Retrieve error codes
     *
     * @return array
     */
    public function get_errors(): array
    {
        return array_keys($this->messages);
    }
    /**
     * Adds a new validator for this class
     *
     * @param string|ValidatorInterface $validator
     * @return HttpInterface
     * @throws InputException
     */
    public function add_validator(string|Validator_Interface $validator): Http_Interface
    {
        if (!$validator instanceof Validator_Interface) {
            throw new Input_Exception('Invalid validator provided to addValidator; ' . 'must be string or Laminas\Validator\ValidatorInterface');
        }
        $this->validators[] = $validator;
        return $this;
    }
    /**
     * Has a file been uploaded ?
     *
     * @param  array|string|null $files
     * @return bool
     */
    public function is_uploaded($files = null): bool
    {
        if (empty($this->files)) {
            return false;
        }
        $file_content = $this->get_file_info($files);
        foreach ($file_content as $file) {
            if (empty($file['name'])) {
                return false;
            }
        }
        return true;
    }
    /**
     * Retrieve additional internal file information for files
     *
     * @param  string $file (Optional) File to get information for
     * @return mixed
     */
    public function get_file_info($file = null): mixed
    {
        $check = [];
        if ($file !== null && isset($this->files[$file])) {
            $check[$file] = $this->files[$file];
            return $check;
        }
        return $this->files;
    }
}