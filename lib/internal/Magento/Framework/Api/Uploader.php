<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Class Uploader specific to uploading images using services
 */
class Uploader extends \Magento\Framework\File\Uploader
{
    /**
     * Avoid running the default constructor specific to FILE upload
     *
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    public function __construct()
    {
    }
    /**
     * Explicitly set the file attributes instead of setting it via constructor
     *
     * @param array $fileAttributes
     * @return void
     * @throws \Exception
     */
    public function process_file_attributes($file_attributes)
    {
        $this->_file = $file_attributes;
        if (!file_exists($this->_file['tmp_name'])) {
            $code = empty($this->_file['tmp_name']) ? self::TMP_NAME_EMPTY : 0;
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new \Exception('File was not processed correctly.', $code);
        } else {
            $this->_file_exists = true;
        }
    }
}