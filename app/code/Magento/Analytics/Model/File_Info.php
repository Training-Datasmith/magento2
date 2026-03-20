<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

/**
 * Contain information about encrypted file.
 */
class File_Info
{
    /**
     * @param string $path
     * @param string $initializationVector
     */
    public function __construct(
        /**
         * Relative path to an encrypted file.
         */
        private $path = '',
        /**
         * Initialization vector that was used for encryption.
         */
        private $initialization_vector = ''
    )
    {
    }
    /**
     * Retrieve path
     *
     * @return string
     */
    public function get_path()
    {
        return $this->path;
    }
    /**
     * Retrieve initialization vector
     *
     * @return string
     */
    public function get_initialization_vector()
    {
        return $this->initialization_vector;
    }
}